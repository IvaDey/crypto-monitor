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
    $query = "SELECT * FROM coins";
    $qr = $db->query($query);

    // Получим курс для каждой монеты
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $url = 'https://bittrex.com/api/v1.1/public/getticker?market=btc-';
    $bnurl = 'https://api.binance.com/api/v1/depth?symbol=';

    while ($row = $qr->fetch_object())
    {
        if ($row->ticker == 'BTC')
        {
            curl_setopt($ch, CURLOPT_URL, 'https://bittrex.com/api/v1.1/public/getticker?market=usdt-btc');
            $req = curl_exec($ch);
            $req = json_decode($req);
            $bid = $req->result->Bid;
        }
        else {
            curl_setopt($ch, CURLOPT_URL, $url.$row->ticker);
            $req = curl_exec($ch);
            $req = json_decode($req);
            if ($req->success)
                $bid = $req->result->Bid;
            else {
                curl_setopt($ch, CURLOPT_URL, $bnurl.$row->ticker.'BTC');
                $req = curl_exec($ch);
                $req = json_decode($req);
                $bid = $req->bids[0][0];
            }
        }

        $query = "UPDATE bids set bid=".$bid." WHERE ticker='".$row->ticker."'";
        print_r($query); echo '<br>';
        if ($db->query($query))
            echo 'succes<br>';
        else {
            print_r($query);
            echo '<br>';
        }
    }

    $db->close();
?>