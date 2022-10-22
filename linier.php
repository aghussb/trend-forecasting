<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Linier</title>
</head>

<body>
    <?php
    session_start();
    $_SESSION['linier']['data'] = (empty($_SESSION['linier']['data'])) ? [] : $_SESSION['linier']['data'];
    $_SESSION['linier']['hitungForecast'] = (!empty($_SESSION['linier']['hitungForecast'])) ? $_SESSION['linier']['hitungForecast'] : 0;
    ?>
    <form action="linier.php" method="post">
        <label for="">Nilai Aktual</label><br>
        <input type="text" name="aktual">
        <input type="submit" name="button" value="submit">
    </form>
    <br>
    <?php
    function prosesLinier()
    {
        foreach ($_SESSION['linier']['data'] as $key => $value) {
            if (count($_SESSION['linier']['data']) % 2 == 0) {
                $_SESSION['linier']['data'][$key]['x'] = (($key > ((count($_SESSION['linier']['data']) / 2) - 1)) ? $key + 1 : $key) - (count($_SESSION['linier']['data']) / 2);
            } else {
                if ($key == ((count($_SESSION['linier']['data']) - 1) / 2)) {
                    $_SESSION['linier']['data'][$key]['x'] = 0;
                } else {
                    $_SESSION['linier']['data'][$key]['x'] = (($key > ((count($_SESSION['linier']['data']) / 2) - 1)) ? $key + 1 - 0.5 : $key + 0.5) - (count($_SESSION['linier']['data']) / 2);
                }
            }

            $_SESSION['linier']['data'][$key]['xy'] = $_SESSION['linier']['data'][$key]['x'] * $_SESSION['linier']['data'][$key]['actual'];
            $_SESSION['linier']['data'][$key]['x2'] = $_SESSION['linier']['data'][$key]['x'] * $_SESSION['linier']['data'][$key]['x'];
        }

        $_SESSION['linier']['actual'] = array_sum(array_column($_SESSION['linier']['data'], 'actual'));
        $_SESSION['linier']['x'] = array_sum(array_column($_SESSION['linier']['data'], 'x'));
        $_SESSION['linier']['xy'] = array_sum(array_column($_SESSION['linier']['data'], 'xy'));
        $_SESSION['linier']['x2'] = array_sum(array_column($_SESSION['linier']['data'], 'x2'));

        $_SESSION['linier']['a'] = $_SESSION['linier']['actual'] / count($_SESSION['linier']['data']);
        $_SESSION['linier']['b'] = $_SESSION['linier']['xy'] / $_SESSION['linier']['x2'];

        foreach ($_SESSION['linier']['data']  as $key => $value) {
            $_SESSION['linier']['data'][$key]['forecast'] = $_SESSION['linier']['a'] + ($_SESSION['linier']['b'] * $_SESSION['linier']['data'][$key]['x']);
            $_SESSION['linier']['data'][$key]['e'] = $_SESSION['linier']['data'][$key]['actual'] - $_SESSION['linier']['data'][$key]['forecast'];
            $_SESSION['linier']['data'][$key]['e2'] = $_SESSION['linier']['data'][$key]['e'] * $_SESSION['linier']['data'][$key]['e'];
            $_SESSION['linier']['data'][$key]['ape'] = abs(($_SESSION['linier']['data'][$key]['actual'] - $_SESSION['linier']['data'][$key]['forecast']) / $_SESSION['linier']['data'][$key]['actual']) * 100;
        }

        $_SESSION['linier']['mse'] = array_sum(array_column($_SESSION['linier']['data'], 'e2')) / count($_SESSION['linier']['data']);
        $_SESSION['linier']['mape'] = array_sum(array_column($_SESSION['linier']['data'], 'ape')) / count($_SESSION['linier']['data']);
    }

    function prosesForecast()
    {
        $_SESSION['linier']['hitungForecast'] = (!empty($_SESSION['linier']['hitungForecast'])) ? $_SESSION['linier']['hitungForecast'] : $_POST['hitung'];
        $_SESSION['linier']['dataForecast'] = [];
        for ($i = 0; $i < $_SESSION['linier']['hitungForecast']; $i++) {
            $objForecast = [
                "x" => ((count($_SESSION['linier']['data']) % 2 == 0) ? count($_SESSION['linier']['data']) / 2 : (count($_SESSION['linier']['data']) - 1) / 2) + $i + 1
            ];

            $objForecast['forecast'] = $_SESSION['linier']['a'] + ($_SESSION['linier']['b'] * $objForecast['x']);

            array_push($_SESSION['linier']['dataForecast'], $objForecast);
        }
    }

    if (!empty($_POST['button'])) {
        $objTabel = [
            "actual" => $_POST['aktual']
        ];

        array_push($_SESSION['linier']['data'], $objTabel);
        if (count($_SESSION['linier']['data']) > 2) {
            prosesLinier();
        }
        if ($_SESSION['linier']['hitungForecast'] != 0) {
            prosesForecast();
        }
    }

    if (!empty($_GET['status'])) {
        unset($_SESSION['linier']['data'][$_GET['index']]);
        $_SESSION['linier']['data'] = array_merge($_SESSION['linier']['data']);
        if (count($_SESSION['linier']['data']) > 2) {
            prosesLinier();
        }

        if ($_SESSION['linier']['hitungForecast'] != 0) {
            prosesForecast();
        }
    }

    ?>
    <table border="1">
        <thead>
            <th>No.</th>
            <th>Actual</th>
            <th>X</th>
            <th>XY</th>
            <th>X²</th>
            <th>Forecast</th>
            <th>Aksi</th>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['linier']['data'] as $key => $value) { ?>
                <tr>
                    <td> <?php echo $key + 1; ?> </td>
                    <td> <?php echo $value['actual'] ?? ""; ?> </td>
                    <td> <?php echo $value['x'] ?? ""; ?> </td>
                    <td> <?php echo $value['xy'] ?? ""; ?> </td>
                    <td> <?php echo $value['x2'] ?? ""; ?> </td>
                    <td> <?php echo $value['forecast'] ?? ""; ?> </td>
                    <td>
                        <a href="linier.php?status=hapus&index=<?php echo $key; ?>">Hapus</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php if (count($_SESSION['linier']['data']) != 0) { ?>
        <label for="">Total Actual : <?php echo $_SESSION['linier']['actual']; ?> </label>
        <br>
        <label for="">Total X : <?php echo $_SESSION['linier']['x']; ?> </label>
        <br>
        <label for="">Total XY : <?php echo $_SESSION['linier']['xy']; ?> </label>
        <br>
        <label for="">Total X² : <?php echo $_SESSION['linier']['x2']; ?> </label>
        <br>
        <label for="">MSE : <?php echo $_SESSION['linier']['mse']; ?> </label>
        <br>
        <label for="">MAPE : <?php echo $_SESSION['linier']['mape']; ?> </label>
        <br>
        <br>
        <form action="" method="post">
            <label for="">Hitung Kedepan </label>
            <br>
            <input type="text" name="hitung">
            <input type="submit" name="buttonHitung" value="submit">
        </form>
        <br>
        <?php

        if (!empty($_POST['buttonHitung'])) {
            prosesForecast();
        }

        ?>

        <?php if ($_SESSION['linier']['hitungForecast'] != 0) { ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>X</th>
                        <th>Forecast</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['linier']['dataForecast'] as $key => $value) { ?>
                        <tr>
                            <td> <?php echo $key + 1; ?> </td>
                            <td> <?php echo $value['x']; ?> </td>
                            <td> <?php echo $value['forecast']; ?> </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>


    <?php } ?>

</body>

</html>