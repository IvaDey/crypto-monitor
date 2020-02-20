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

    // Получим текущую дату и время
    $res['date'] = date('Y\-m\-d h:m:s');

    // Установим базовые вложения
    $res['btc_base_value'] = 8.4;
    $res['usd_base_value'] = 87719.29;
    $res['rub_base_value'] = 5000000;

    // Получаем список монет
    $query = "SELECT * FROM coins";
    $qr = $db->query($query);

    // Запишем их количество
    $res['pos_count'] = $qr->num_rows;

    // Получим и просчитаем оценку и курсы для каждой монеты
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $url = 'https://bittrex.com/api/v1.1/public/getticker?market=btc-';
    $bnurl = 'https://api.binance.com/api/v1/depth?symbol=';

    $res['BTC_value'] = 0;
    while ($row = $qr->fetch_object())
    {
        if ($row->ticker == 'BTC')
            $res['BTC_value'] += $row->amount;
        else {
            curl_setopt($ch, CURLOPT_URL, $url.$row->ticker);
            $req = curl_exec($ch);
            $req = json_decode($req);
            if ($req->success)
                $res['BTC_value'] += $row->amount * $req->result->Bid;
            else {
                curl_setopt($ch, CURLOPT_URL, $bnurl.$row->ticker.'BTC');
                $req = curl_exec($ch);
                $req = json_decode($req);
                $res['BTC_value'] += $row->amount * $req->bids[0][0];
            }
        }
    }

    // Получим курс биткоина
    $url = 'https://bittrex.com/api/v1.1/public/getticker?market=usdt-btc';
    curl_setopt($ch, CURLOPT_URL, $url);
    $req = curl_exec($ch);
    $req = json_decode($req);
    $res['BTC_USD_rate'] = $req->result->Bid;

    // Получим курс эфира к доллару
    $url = 'https://bittrex.com/api/v1.1/public/getticker?market=usdt-eth';
    curl_setopt($ch, CURLOPT_URL, $url);
    $req = curl_exec($ch);
    $req = json_decode($req);
    $res['ETH_USD_rate'] = $req->result->Bid;

    // Получим курс доллара к рублю
    $url = 'http://www.cbr.ru/scripts/XML_daily.asp';
    $req = file_get_contents($url);
    $req = new SimpleXMLElement($req);
    foreach ($req->Valute as $valute)
        {
            if ($valute->CharCode == 'USD')
                $usd = (string)$valute->Value;
        }
    $usd = str_replace(',','.',$usd);
    $usd = floatval($usd);
    $res['USD_RUB_rate'] = $usd;

    // Подсчитаем оценку портфеля в долларах и рублях
    $res['USD_value'] = $res['BTC_value']*$res['BTC_USD_rate'];
    $res['RUB_value'] = $res['USD_value']*$res['USD_RUB_rate'];

    // Подсчитаем Profit/Loss
    $res['BTC_profit'] = $res['BTC_value'] - $res['btc_base_value'];
    $res['USD_profit'] = $res['USD_value'] - $res['usd_base_value'];
    $res['RUB_profit'] = $res['RUB_value'] - $res['rub_base_value'];

    curl_close($ch);

    $query = "INSERT INTO general (date, pos_count, BTC_value, USD_value, RUB_value,";
    $query .= "BTC_USD_rate, ETH_USD_rate, USD_RUB_rate, BTC_profit, USD_profit, RUB_profit,";
    $query .= "btc_base_value, usd_base_value, rub_base_value) VALUES(";

    $query .= "'".$res['date']."', ";
    $query .= $res['pos_count'].", ";
    $query .= $res['BTC_value'].", ";
    $query .= $res['USD_value'].", ";
    $query .= $res['RUB_value'].", ";
    $query .= $res['BTC_USD_rate'].", ";
    $query .= $res['ETH_USD_rate'].", ";
    $query .= $res['USD_RUB_rate'].", ";
    $query .= $res['BTC_profit'].", ";
    $query .= $res['USD_profit'].", ";
    $query .= $res['RUB_profit'].", ";
    $query .= $res['btc_base_value'].", ";
    $query .= $res['usd_base_value'].", ";
    $query .= $res['rub_base_value'].")";

    $qr = $db->query($query);
    if ($qr)
        echo 'success';
    else echo 'failed';

    $db->close();

//    $result = json_encode($res);
//    print_r($result);
?>