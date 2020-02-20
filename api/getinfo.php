<?php
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $url = 'https://bittrex.com/api/v1.1/public/getticker?market=';

    // Get BTC Bid
    $market = 'usdt-btc';
    curl_setopt($ch, CURLOPT_URL, $url.$market);
    $req = curl_exec($ch);
    $req = json_decode($req)->result;
    $result['exchange']['btcusd'] = round($req->Bid, 2);

    // Get ETH Bid
    $market = 'usdt-eth';
    curl_setopt($ch, CURLOPT_URL, $url.$market);
    $req = curl_exec($ch);
    curl_close($ch);
    $req = json_decode($req)->result;
    $result['exchange']['ethusd'] = round($req->Bid, 2);

    // Получение курса доллара
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

    // Get BTC, ETH Bid in RUB and USD in RUB
    $result['exchange']['btcrub'] = $result['exchange']['btcusd']*$usd;
    $result['exchange']['ethrub'] = $result['exchange']['ethusd']*$usd;
    $result['exchange']['usdrub'] = $usd;

    // Получение данных из БД
    $user = 'root';
    $password = 'root';
    $db = 'crypto';
    $host = 'localhost';
    $port = 3306;
    $query = "SELECT * FROM coins";

    $db = new mysqli($host,$user,$password,$db,$port);
    $qr = $db->query($query);
    $result['altfolio']['count'] = $qr->num_rows;
    $count = 0;
    while ($row = $qr->fetch_object())
    {
        $result['altfolio'][$count]['coin_name'] = $row->coinName;
        $result['altfolio'][$count]['ticker'] = $row->ticker;
        $result['altfolio'][$count]['amount'] = $row->amount;
        $result['altfolio'][$count]['price'] = $row->price;
        $count++;
    }

    $db->close();

    // Формирование ответа
    $ans = json_encode($result);
    print_r($ans);
?>