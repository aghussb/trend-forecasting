<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eksponential</title>
</head>

<body>
    <?php
    session_start();
    $_SESSION['eksponential']['data'] = (empty($_SESSION['eksponential']['data'])) ? [] : $_SESSION['eksponential']['data'];
    $_SESSION['eksponential']['hitungForecast'] = (!empty($_SESSION['eksponential']['hitungForecast'])) ? $_SESSION['eksponential']['hitungForecast'] : 0;
    ?>
    <form action="eksponential.php" method="post">
        <label for="">Nilai Aktual</label><br>
        <input type="text" name="aktual">
        <input type="submit" name="button" value="submit">
    </form>
    <br>
    <?php
    function prosesEksponensial()
    {
        foreach ($_SESSION['eksponential']['data'] as $key => $value) {
            if (count($_SESSION['eksponential']['data']) % 2 == 0) {
                $_SESSION['eksponential']['data'][$key]['x'] = (($key > ((count($_SESSION['eksponential']['data']) / 2) - 1)) ? $key + 1 : $key) - (count($_SESSION['eksponential']['data']) / 2);
            } else {
                if ($key == ((count($_SESSION['eksponential']['data']) - 1) / 2)) {
                    $_SESSION['eksponential']['data'][$key]['x'] = 0;
                } else {
                    $_SESSION['eksponential']['data'][$key]['x'] = (($key > ((count($_SESSION['eksponential']['data']) / 2) - 1)) ? $key + 1 - 0.5 : $key + 0.5) - (count($_SESSION['eksponential']['data']) / 2);
                }
            }

            $_SESSION['eksponential']['data'][$key]['logy'] = log($_SESSION['eksponential']['data'][$key]['actual'], 10);
            $_SESSION['eksponential']['data'][$key]['x2'] = $_SESSION['eksponential']['data'][$key]['x'] * $_SESSION['eksponential']['data'][$key]['x'];
            $_SESSION['eksponential']['data'][$key]['xlogy'] = $_SESSION['eksponential']['data'][$key]['x'] * $_SESSION['eksponential']['data'][$key]['logy'];
        }

        $_SESSION['eksponential']['actual'] = array_sum(array_column($_SESSION['eksponential']['data'], 'actual'));
        $_SESSION['eksponential']['x'] = array_sum(array_column($_SESSION['eksponential']['data'], 'x'));
        $_SESSION['eksponential']['logy'] = array_sum(array_column($_SESSION['eksponential']['data'], 'logy'));
        $_SESSION['eksponential']['x2'] = array_sum(array_column($_SESSION['eksponential']['data'], 'x2'));
        $_SESSION['eksponential']['xlogy'] = array_sum(array_column($_SESSION['eksponential']['data'], 'xlogy'));

        $_SESSION['eksponential']['logA'] = $_SESSION['eksponential']['logy'] / count($_SESSION['eksponential']['data']);
        $_SESSION['eksponential']['logB'] = $_SESSION['eksponential']['xlogy'] / $_SESSION['eksponential']['x2'];

        foreach ($_SESSION['eksponential']['data']  as $key => $value) {
            $_SESSION['eksponential']['data'][$key]['logForecast'] = $_SESSION['eksponential']['logA'] + ($_SESSION['eksponential']['logB'] * $_SESSION['eksponential']['data'][$key]['x']);
            $_SESSION['eksponential']['data'][$key]['forecast'] = pow(10, $_SESSION['eksponential']['data'][$key]['logForecast']);
            $_SESSION['eksponential']['data'][$key]['e'] = $_SESSION['eksponential']['data'][$key]['actual'] - $_SESSION['eksponential']['data'][$key]['forecast'];
            $_SESSION['eksponential']['data'][$key]['e2'] = $_SESSION['eksponential']['data'][$key]['e'] * $_SESSION['eksponential']['data'][$key]['e'];
            $_SESSION['eksponential']['data'][$key]['ape'] = abs(($_SESSION['eksponential']['data'][$key]['actual'] - $_SESSION['eksponential']['data'][$key]['forecast']) / $_SESSION['eksponential']['data'][$key]['actual']) * 100;
        }

        $_SESSION['eksponential']['mse'] = array_sum(array_column($_SESSION['eksponential']['data'], 'e2')) / count($_SESSION['eksponential']['data']);
        $_SESSION['eksponential']['mape'] = array_sum(array_column($_SESSION['eksponential']['data'], 'ape')) / count($_SESSION['eksponential']['data']);
    }

    function prosesForecast()
    {
        $_SESSION['eksponential']['hitungForecast'] = (!empty($_SESSION['eksponential']['hitungForecast'])) ? $_SESSION['eksponential']['hitungForecast'] : $_POST['hitung'];
        $_SESSION['eksponential']['dataForecast'] = [];
        for ($i = 0; $i < $_SESSION['eksponential']['hitungForecast']; $i++) {
            $objForecast = [
                "x" => ((count($_SESSION['eksponential']['data']) % 2 == 0) ? count($_SESSION['eksponential']['data']) / 2 : (count($_SESSION['eksponential']['data']) - 1) / 2) + $i + 1
            ];

            $objForecast['logForecast'] = $_SESSION['eksponential']['logA'] + ($_SESSION['eksponential']['logB'] * $objForecast['x']);
            $objForecast['forecast'] = pow(10, $objForecast['logForecast']);

            array_push($_SESSION['eksponential']['dataForecast'], $objForecast);
        }
    }

    if (!empty($_POST['button'])) {
        $objTabel = [
            "actual" => $_POST['aktual']
        ];

        array_push($_SESSION['eksponential']['data'], $objTabel);
        if (count($_SESSION['eksponential']['data']) > 2) {
            prosesEksponensial();
        }

        if ($_SESSION['eksponential']['hitungForecast'] != 0) {
            prosesForecast();
        }
    }

    if (!empty($_GET['status'])) {
        unset($_SESSION['eksponential']['data'][$_GET['index']]);
        $_SESSION['eksponential']['data'] = array_merge($_SESSION['eksponential']['data']);
        if (count($_SESSION['eksponential']['data']) > 2) {
            prosesEksponensial();
        }

        if ($_SESSION['eksponential']['hitungForecast'] != 0) {
            prosesForecast();
        }
    }

    ?>
    <table border="1">
        <thead>
            <th>No.</th>
            <th>Actual</th>
            <th>X</th>
            <th>Log Y</th>
            <th>X²</th>
            <th>X.Log Y</th>
            <th>Log Forecast</th>
            <th>Forecast</th>
            <th>Aksi</th>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['eksponential']['data'] as $key => $value) { ?>
                <tr>
                    <td> <?php echo $key + 1; ?> </td>
                    <td> <?php echo $value['actual'] ?? ""; ?> </td>
                    <td> <?php echo $value['x'] ?? ""; ?> </td>
                    <td> <?php echo $value['logy'] ?? ""; ?> </td>
                    <td> <?php echo $value['x2'] ?? ""; ?> </td>
                    <td> <?php echo $value['xlogy'] ?? ""; ?> </td>
                    <td> <?php echo $value['logForecast'] ?? ""; ?> </td>
                    <td> <?php echo $value['forecast'] ?? ""; ?> </td>
                    <td>
                        <a href="eksponential.php?status=hapus&index=<?php echo $key; ?>">Hapus</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php if (count($_SESSION['eksponential']['data']) != 0) { ?>
        <label for="">Total Actual : <?php echo $_SESSION['eksponential']['actual']; ?> </label>
        <br>
        <label for="">Total X : <?php echo $_SESSION['eksponential']['x']; ?> </label>
        <br>
        <label for="">Total Log Y : <?php echo $_SESSION['eksponential']['logy']; ?> </label>
        <br>
        <label for="">Total X² : <?php echo $_SESSION['eksponential']['x2']; ?> </label>
        <br>
        <label for="">Total X.Log Y : <?php echo $_SESSION['eksponential']['xlogy']; ?> </label>
        <br>
        <label for="">MSE : <?php echo $_SESSION['eksponential']['mse']; ?> </label>
        <br>
        <label for="">MAPE : <?php echo $_SESSION['eksponential']['mape']; ?> </label>
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

        <?php if ($_SESSION['eksponential']['hitungForecast'] != 0) { ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>X</th>
                        <th>Log Forecast</th>
                        <th>Forecast</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['eksponential']['dataForecast'] as $key => $value) { ?>
                        <tr>
                            <td> <?php echo $key + 1; ?> </td>
                            <td> <?php echo $value['x']; ?> </td>
                            <td> <?php echo $value['logForecast']; ?> </td>
                            <td> <?php echo $value['forecast']; ?> </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>


    <?php } ?>

</body>

</html>