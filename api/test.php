<?php
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    $user = 'root';
    $password = 'root';
    $db = 'crypto';
    $host = 'localhost';
    $port = 3306;
    $db = new mysqli($host,$user,$password,$db,$port);

    // Получаем список монет
    $query = "SELECT * FROM history";
    $qr = $db->query($query);

    $total = 0;
    while ($row = $qr->fetch_object())
        $total += $row->usd_coast;

    print_r($total);

    $db->close();
?>