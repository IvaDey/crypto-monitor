<?php
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // Получение данных из БД
    $user = 'root';
    $password = 'root';
    $db = 'crypto';
    $host = 'localhost';
    $port = 3306;
    $query = "SELECT * FROM general";

    $db = new mysqli($host, $user, $password, $db, $port);
    $qr = $db->query($query);
    while ($t = $qr->fetch_object())
        $row = $t;

    $result['general']['pos_count'] = $row->pos_count;
    $result['general']['BTC_value'] = floatval($row->BTC_value);
    $result['general']['USD_value'] = floatval($row->USD_value);
    $result['general']['RUB_value'] = floatval($row->RUB_value);
    $result['general']['BTC_profit'] = floatval($row->BTC_profit);
    $result['general']['USD_profit'] = floatval($row->USD_profit);
    $result['general']['RUB_profit'] = floatval($row->RUB_profit);
    $result['general']['BTC_percent_profit'] = floatval(($row->BTC_profit * 100) / $row->btc_base_value);
    $result['general']['USD_percent_profit'] = floatval(($row->USD_profit * 100) / $row->usd_base_value);
    $result['general']['RUB_percent_profit'] = floatval(($row->RUB_profit * 100) / $row->rub_base_value);

    $result['exchange']['btcusd'] = floatval($row->BTC_USD_rate);
    $result['exchange']['btcrub'] = floatval($row->BTC_USD_rate * $row->USD_RUB_rate);
    $result['exchange']['ethusd'] = floatval($row->ETH_USD_rate);
    $result['exchange']['ethrub'] = floatval($row->ETH_USD_rate * $row->USD_RUB_rate);
    $result['exchange']['usdrub'] = floatval($row->USD_RUB_rate);

    $query = "SELECT *, (SELECT bid FROM bids WHERE ticker = history.ticker) as bid FROM history";

    $qr = $db->query($query);

    while ($row = $qr->fetch_object())
    {
        $id = $row->ticker;

        $result['history'][$id]['ticker'] = $row->ticker;
        $result['history'][$id]['coin_name'] = $row->coin_name;
        $result['history'][$id]['amount'] = $row->amount;
        $result['history'][$id]['price'] = $row->price;
        $result['history'][$id]['usd_price'] = $row->usd_price;
        $result['history'][$id]['coast'] = $row->coast;
        $result['history'][$id]['usd_coast'] = $row->usd_coast;
        $result['history'][$id]['date'] = $row->date;
        $result['history'][$id]['last_bid'] = $row->bid;
        $result['history'][$id]['order_type'] = $row->order_type;
        if ($row->ticker == 'BTC')
            $result['history'][$id]['BTC_value'] = $row->amount;
        else $result['history'][$id]['BTC_value'] = $row->amount * $row->bid;
    }

    $db->close();

    // Формирование ответа
    $ans = json_encode($result);
    print_r($ans);
?>