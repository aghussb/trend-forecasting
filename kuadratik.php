<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuadratik</title>
</head>

<body>
    <?php
    session_start();
    $_SESSION['kuadratik']['data'] = (empty($_SESSION['kuadratik']['data'])) ? [] : $_SESSION['kuadratik']['data'];
    $_SESSION['eksponential']['hitungForecast'] = (!empty($_SESSION['eksponential']['hitungForecast'])) ? $_SESSION['eksponential']['hitungForecast'] : 0;
    ?>
    <form action="kuadratik.php" method="post">
        <label for="">Nilai Aktual</label><br>
        <input type="text" name="aktual">
        <input type="submit" name="button" value="submit">
    </form>
    <br>
    <?php
    function prosesKuadratik()
    {
        foreach ($_SESSION['kuadratik']['data'] as $key => $value) {
            if (count($_SESSION['kuadratik']['data']) % 2 == 0) {
                $_SESSION['kuadratik']['data'][$key]['x'] = (($key > ((count($_SESSION['kuadratik']['data']) / 2) - 1)) ? $key + 1 : $key) - (count($_SESSION['kuadratik']['data']) / 2);
            } else {
                if ($key == ((count($_SESSION['kuadratik']['data']) - 1) / 2)) {
                    $_SESSION['kuadratik']['data'][$key]['x'] = 0;
                } else {
                    $_SESSION['kuadratik']['data'][$key]['x'] = (($key > ((count($_SESSION['kuadratik']['data']) / 2) - 1)) ? $key + 1 - 0.5 : $key + 0.5) - (count($_SESSION['kuadratik']['data']) / 2);
                }
            }

            $_SESSION['kuadratik']['data'][$key]['xy'] = $_SESSION['kuadratik']['data'][$key]['x'] * $_SESSION['kuadratik']['data'][$key]['actual'];
            $_SESSION['kuadratik']['data'][$key]['x2'] = $_SESSION['kuadratik']['data'][$key]['x'] * $_SESSION['kuadratik']['data'][$key]['x'];
            $_SESSION['kuadratik']['data'][$key]['yx2'] = $_SESSION['kuadratik']['data'][$key]['actual'] * $_SESSION['kuadratik']['data'][$key]['x2'];
            $_SESSION['kuadratik']['data'][$key]['x4'] = $_SESSION['kuadratik']['data'][$key]['x'] * $_SESSION['kuadratik']['data'][$key]['x'] * $_SESSION['kuadratik']['data'][$key]['x'] * $_SESSION['kuadratik']['data'][$key]['x'];
        }

        $_SESSION['kuadratik']['actual'] = array_sum(array_column($_SESSION['kuadratik']['data'], 'actual'));
        $_SESSION['kuadratik']['x'] = array_sum(array_column($_SESSION['kuadratik']['data'], 'x'));
        $_SESSION['kuadratik']['xy'] = array_sum(array_column($_SESSION['kuadratik']['data'], 'xy'));
        $_SESSION['kuadratik']['x2'] = array_sum(array_column($_SESSION['kuadratik']['data'], 'x2'));
        $_SESSION['kuadratik']['yx2'] = array_sum(array_column($_SESSION['kuadratik']['data'], 'yx2'));
        $_SESSION['kuadratik']['x4'] = array_sum(array_column($_SESSION['kuadratik']['data'], 'x4'));

        $_SESSION['kuadratik']['c'] = ((count($_SESSION['kuadratik']['data'])*$_SESSION['kuadratik']['yx2'])-($_SESSION['kuadratik']['x2']*$_SESSION['kuadratik']['actual']))/((count($_SESSION['kuadratik']['data'])*$_SESSION['kuadratik']['x4'])-pow($_SESSION['kuadratik']['x2'],2));
        $_SESSION['kuadratik']['a'] = ($_SESSION['kuadratik']['actual'] - ($_SESSION['kuadratik']['c'] * $_SESSION['kuadratik']['x2'])) / count($_SESSION['kuadratik']['data']);
        $_SESSION['kuadratik']['b'] = $_SESSION['kuadratik']['xy'] / $_SESSION['kuadratik']['x2'];
        
        foreach ($_SESSION['kuadratik']['data']  as $key => $value) {
            $_SESSION['kuadratik']['data'][$key]['forecast'] = $_SESSION['kuadratik']['a'] + ($_SESSION['kuadratik']['b'] * $_SESSION['kuadratik']['data'][$key]['x'])+($_SESSION['kuadratik']['c']*$_SESSION['kuadratik']['data'][$key]['x2']);
            $_SESSION['kuadratik']['data'][$key]['e'] = $_SESSION['kuadratik']['data'][$key]['actual'] - $_SESSION['kuadratik']['data'][$key]['forecast'];
            $_SESSION['kuadratik']['data'][$key]['e2'] = $_SESSION['kuadratik']['data'][$key]['e'] * $_SESSION['kuadratik']['data'][$key]['e'];
            $_SESSION['kuadratik']['data'][$key]['ape'] = abs(($_SESSION['kuadratik']['data'][$key]['actual'] - $_SESSION['kuadratik']['data'][$key]['forecast']) / $_SESSION['kuadratik']['data'][$key]['actual']) * 100;
        }

        $_SESSION['kuadratik']['mse'] = array_sum(array_column($_SESSION['kuadratik']['data'], 'e2')) / count($_SESSION['kuadratik']['data']);
        $_SESSION['kuadratik']['mape'] = array_sum(array_column($_SESSION['kuadratik']['data'], 'ape')) / count($_SESSION['kuadratik']['data']);
    }

    function prosesForecast()
    {
        $_SESSION['kuadratik']['hitungForecast'] = (!empty($_SESSION['kuadratik']['hitungForecast'])) ? $_SESSION['kuadratik']['hitungForecast'] : $_POST['hitung'];
        $_SESSION['kuadratik']['dataForecast'] = [];
        for ($i = 0; $i < $_SESSION['kuadratik']['hitungForecast']; $i++) {
            $objForecast = [
                "x" => ((count($_SESSION['kuadratik']['data']) % 2 == 0) ? count($_SESSION['kuadratik']['data']) / 2 : (count($_SESSION['kuadratik']['data']) - 1) / 2) + $i + 1
            ];

            $objForecast['x2'] = $objForecast['x']*$objForecast['x'];
            $objForecast['forecast'] = $_SESSION['kuadratik']['a'] + ($_SESSION['kuadratik']['b'] * $objForecast['x'])+($_SESSION['kuadratik']['c']*$objForecast['x2']);

            array_push($_SESSION['kuadratik']['dataForecast'], $objForecast);
        }
    }

    if (!empty($_POST['button'])) {
        $objTabel = [
            "actual" => $_POST['aktual']
        ];

        array_push($_SESSION['kuadratik']['data'], $objTabel);

        if (count($_SESSION['kuadratik']['data']) > 2) {
            prosesKuadratik();
        }

        if (!empty($_SESSION['kuadratik']['hitungForecast'])) {
            if ($_SESSION['kuadratik']['hitungForecast'] != 0) {
                prosesForecast();
            }
        }
    }

    if (!empty($_GET['status'])) {
        unset($_SESSION['kuadratik']['data'][$_GET['index']]);
        $_SESSION['kuadratik']['data'] = array_merge($_SESSION['kuadratik']['data']);
        if (count($_SESSION['kuadratik']['data']) > 2) {
            prosesKuadratik();
        }

        if (!empty($_SESSION['kuadratik']['hitungForecast'])) {
            if ($_SESSION['kuadratik']['hitungForecast'] != 0) {
                prosesForecast();
            }
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
            <th>YX²</th>
            <th>X⁴</th>
            <th>Forecast</th>
            <th>Aksi</th>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['kuadratik']['data'] as $key => $value) { ?>
                <tr>
                    <td> <?php echo $key + 1; ?> </td>
                    <td> <?php echo $value['actual'] ?? ""; ?> </td>
                    <td> <?php echo $value['x'] ?? ""; ?> </td>
                    <td> <?php echo $value['xy'] ?? ""; ?> </td>
                    <td> <?php echo $value['x2'] ?? ""; ?> </td>
                    <td> <?php echo $value['yx2'] ?? ""; ?> </td>
                    <td> <?php echo $value['x4'] ?? ""; ?> </td>
                    <td> <?php echo $value['forecast'] ?? ""; ?> </td>
                    <td>
                        <a href="kuadratik.php?status=hapus&index=<?php echo $key; ?>">Hapus</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php if (count($_SESSION['kuadratik']['data']) != 0) { ?>
        <label for="">Total Actual : <?php echo $_SESSION['kuadratik']['actual']; ?> </label>
        <br>
        <label for="">Total X : <?php echo $_SESSION['kuadratik']['x']; ?> </label>
        <br>
        <label for="">Total XY : <?php echo $_SESSION['kuadratik']['xy']; ?> </label>
        <br>
        <label for="">Total X² : <?php echo $_SESSION['kuadratik']['x2']; ?> </label>
        <br>
        <label for="">Total YX² : <?php echo $_SESSION['kuadratik']['yx2']; ?> </label>
        <br>
        <label for="">Total X⁴ : <?php echo $_SESSION['kuadratik']['x4']; ?> </label>
        <br>
        <label for="">MSE : <?php echo $_SESSION['kuadratik']['mse']; ?> </label>
        <br>
        <label for="">MAPE : <?php echo $_SESSION['kuadratik']['mape']; ?> </label>
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

        <?php if (!empty($_SESSION['kuadratik']['hitungForecast'])) { ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>X</th>
                        <th>X²</th>
                        <th>Forecast</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['kuadratik']['dataForecast'] as $key => $value) { ?>
                        <tr>
                            <td> <?php echo $key + 1; ?> </td>
                            <td> <?php echo $value['x']; ?> </td>
                            <td> <?php echo $value['x2']; ?> </td>
                            <td> <?php echo $value['forecast']; ?> </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>


    <?php } ?>

</body>

</html>