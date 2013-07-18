<?include_once('cms/public/api.php');
include_once('cms/mods/cabinet/cabinet_class.php');
$cab = new cabinet();
if ($cab->ac->object['head'] != $cab->subject['id']) exit();
?>
<!DOCTYPE html>

<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

  <title>Promzona.kz</title>

  <!-- Meta block -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keywords" content="">

  <!-- Bootstrap -->
    <link href="/css/bootstrap.min.css" rel="stylesheet" />

  <!-- CSS -->
    <link href="/css/print.css" media="screen" rel="stylesheet" />
    <link href="/css/print.css" media="print" rel="stylesheet" />

</head>

<body>
<?$phones = explode("\n", $cab->subject['Телефон'])?>

    <div class="order-print">

        <div class="text-block">
            <p><b>Внимание! Просим Вас обязательно сообщить нам об оплате данного счёта на billing@promzona.kz, пришлите, пожалуйста, скан-копию платёжного документа</b></p>
            <p>В назначении платежа указывайте: Оплата счёта за номером, от даты, за услуги интернет рекламы.</p>
            <p>Для оплаты данного счёта не требуется заключение договора.</p>
            <p>Оригиналы документов для бухгалтерии будут отправлены КазПочтой после получения оплаты.</p>
        </div>

        <table class="table table-condensed table-bordered">
            <colgroup>
                <col width="50%" />
                <col width="25%" />
                <col width="25%" />
            </colgroup>
            <caption>
                <p><b>Образец платёжного поручения</b></p>
            </caption>
            <thead>
                <tr>
                    <th><b>Бенефициар:<br /> Товарищество с ограниченной ответственностью "PROMPORTAL KAZAKHSTAN"</b><br /> БИН: 120940009723</th>
                    <th><b>ИИК<br /> KZ609261802166618000</b></th>
                    <th><b>Кбе<br /> 17</b></th>
                </tr>
                <tr>
                    <td>Банк бенефициара:<br /> АО "Казкоммерцбанк" г.Алматы</td>
                    <td><b>БИК<br /> KZKOKZKX</b></td>
                    <td><b>Код назначения платежа<br /> 851</b></td>
                </tr>
            </thead>
        </table>

        <div class="order">

            <h1>Счёт на оплату № <span class="order-number">PZ<?=$cab->ac->object['Номер']?></span> от <?=$cab->strings->date(date('Y-m-d',$cab->ac->object['sort']))?></h1>

            <table class="order-description">
                <colgroup>
                    <col width="15%" />
                    <col width="85%" />
                </colgroup>
                <tr>
                    <td>Поставщик:</td>
                    <td><b>БИН: 120940009723, Товарищество с ограниченной ответственностью "PROMPORTAL KAZAKHSTAN", Казахстан, 050008, г.Алматы, ул. Брусиловского, 163, кв. 524, мобильный тел.:87011020161, 87773632280, 
e-mail:billing@promzona.kz</b></td>
                </tr>
                <tr>
                    <td>Покупатель:</td>
                    <td><b>БИН / ИНН <?=$cab->ac->object['РНН']?>, 
                        <?=$cab->ac->object['Плательщик']?>, 
                        <?=$cab->subject['Адрес']?>, 
                        тел: <?=join(", ", $phones)?>, 
                        e-mail:<?=$cab->subject['Email']?></b></td>
                </tr>
                <tr>
                    <td>Договор:</td>
                    <td><b>"ПУБЛИЧНАЯ ОФЕРТА": № 565659</b></td>
                </tr>
            </table>

            <table class="table table-condensed table-bordered">
                <colgroup>
                    <col width="" />
                    <col width="" />
                    <col width="" />
                    <col width="" />
                    <col width="" />
                    <col width="" />
                </colgroup>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Наименование</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><?=$cab->ac->object['Название']?></td>
                        <td><?=number_format($cab->ac->object['Сумма'], 2, ',', ' ')?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th align="right" colspan="2">Итого:<br /> Без налога (НДС)</th>
                        <th><?=number_format($cab->ac->object['Сумма'], 2, ',', ' ')?></th>
                    </tr>
                    <tr>
                        <th colspan="3">
                            Всего наименований 1, на сумму <?=number_format($cab->ac->object['Сумма'], 2, ',', ' ')?> KZT<br /> 
                            <!-- <b>Всего к оплате: Одиннадцать тысяч пятьсот тенге 00 тиын</b> -->
                        </th>
                    </tr>
                </tfoot>
            </table>
            <p>Всего к оплате: <?=num2tenge::doit($cab->ac->object['Сумма']);?></p>

            <button type="button" onClick="window.print()">Печать</button>

        </div>

    </div>

</body>
</html>