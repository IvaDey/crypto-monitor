var load_info = function () {
    $.ajax({
        url: "api/test_get_api.php",
        type: 'post',
        dataType: 'json',
        success: function (data) {
            var btcusd;
            $.each(data.exchange, function (key, value) {
                $('#' + key).html(value.toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 2}));
                if (key == 'btcusd')
                    btcusd = value;
            })

            $.each(data.general, function (key, value) {
                $('#' + key).html(value.toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 2}));
                if (value < 0)
                    $('#' + key).css("color", "red");
            })

            var num = 1;
            $.each(data.history, function (key, value) {
                var val;

                // Добавляем шапку монеты
                var token = '<div class="token-info-head">';
                token += '<span class="tih-1">' + num + '</span>';

                token += '<span class="tih-2">' + value.coin_name + '</span>';
                token += '<span class="tih-3">' + value.ticker + '</span>';

                val = Number(value.amount).toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 2});
                token += '<span class="tih-4">' + val + '</span>';

                val = value.coast.toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 4});
                usd = Number(value.usd_coast).toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 2});
                token += '<span class="tih-5">' + val + ' BTC / $ ' + usd + '</span>';

                val = value.BTC_value.toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 4});
                usd = (value.BTC_value * btcusd).toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 2});
                token += '<span class="tih-6">' + val + ' BTC / $ ' + usd + '</span>';

                val = (value.BTC_value - value.coast).toLocaleString('ru', {
                    style: 'decimal',
                    maximumFractionDigits: 4
                });
                usd = (value.BTC_value * btcusd - value.usd_coast).toLocaleString('ru', {
                    style: 'decimal',
                    maximumFractionDigits: 2
                });
                if (value.BTC_value - value.coast < 0 || value.BTC_value * btcusd - value.usd_coast < 0)
                    token += '<span class="tih-7" style="color: red">' + val + ' BTC / $ ' + usd + '</span>';
                else token += '<span class="tih-7">' + val + ' BTC / $ ' + usd + '</span>';

                val = value.BTC_value - value.coast;
                val = (val * 100 / value.coast).toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 2});
                usd = (value.BTC_value * btcusd - value.usd_coast) * 100 / value.usd_coast;
                usd = usd.toLocaleString('ru', {style: 'decimal', maximumFractionDigits: 2});
                if (value.BTC_value - value.coast < 0 || value.BTC_value * btcusd - value.usd_coast < 0)
                    token += '<span class="tih-8" style="color: red">' + val + ' % / ' + usd + ' %</span>';
                else token += '<span class="tih-8">' + val + ' % / ' + usd + ' %</span>';

                token += '</div>';

                $('#open-pos').append(token);

                // Добавляем спойлер со списком ордеров к монете
                token = '<div class="token-info-body">' +
                    '<div class="tib-row">' +
                    '<span class="tib-1">Тип</span>' +
                    '<span class="tib-2">Дата</span>' +
                    '<span class="tib-3">Количество</span>' +
                    '<span class="tib-4">Цена (BTC / USD)</span>' +
                    '<span class="tib-5">Стоимость (BTC / USD)</span>' +
                    '<span class="tib-6">Оценка (BTC / USD)</span>' +
                    '<span class="tib-7">Profit/Loss (BTC / USD)</span>' +
                    '<span class="tib-8">Profit/Loss (%BTC / %USD)</span>' +
                    '</div>'

                token += '<div class="tib-row">' +
                    '<span class="tib-1">' + value.order_type + '</span>' +
                    '<span class="tib-2">' + value.date + '</span>' +
                    '<span class="tib-3">0.3 BTC</span>' +
                    '<span class="tib-4">1 BTC / $8,561</span>' +
                    '<span class="tib-5">0.3 BTC / $2,568.3</span>' +
                    '<span class="tib-6">0.3 BTC / $2,568.3</span>' +
                    '<span class="tib-7">0 BTC / $0</span>' +
                    '<span class="tib-8">0% / 0%</span>' +
                    '</div>';
                token += '</div>';
                $('#open-pos').append(token);

                num++;
            })
            $('.token-info-head').click(function () {
                $(this).next().slideToggle();
            });
        }
    });
}

var update_info = function () {
    $.ajax({
        url: 'api/update_bids.php',
        success: function () {
            $.ajax({
                url: 'api/update_history.php',
                success: function () {
                    var hd = '<div class="token-info-head" style="padding: 15px 0;">\n' +
                        '                <span class="tih-1">№</span>\n' +
                        '                <span class="tih-2">Название</span>\n' +
                        '                <span class="tih-3">Тикер</span>\n' +
                        '                <span class="tih-4">Количество</span>\n' +
                        '                <span class="tih-5">Стоимость (BTC / USD)</span>\n' +
                        '                <span class="tih-6">Оценка (BTC / USD)</span>\n' +
                        '                <span class="tih-7">Profit/Loss (BTC / USD)</span>\n' +
                        '                <span class="tih-8">Profit/Loss (%BTC / %USD)</span>\n' +
                        '            </div><div></div>';
                    $('token-info').html(hd);
                    load_info();
                }
            })
        }
    })
}

$('document').ready(function(){
    $('#portfolio').click(function () {
        $('#chart-info').hide();
        $('#portfolio-info').show();
    });

    $('#chart').click(function () {
        $('#portfolio-info').hide();
        $('#chart-info').show();
    });

    $('#add-coin').click(function () {
        $('#mask').fadeToggle();
        $('#modal-add').fadeToggle();
    });

    $('#closeBtn').click(function () {
        $('#mask').fadeToggle();
        $('#modal-add').fadeToggle();
    });

    $('#send').click(function () {
        var coinName = $()
        return false;
    });

    load_info();
    update_info();
})