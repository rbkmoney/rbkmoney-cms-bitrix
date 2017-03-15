<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

CModule::IncludeModule('sale');

$APPLICATION->SetPageProperty("title", "Информация о заказе");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Оплата заказа");

$orderID = $_REQUEST['orderId'];
$order = CSaleOrder::GetByID($orderID);
?>

<?php if (!$order): ?>
    Заказ с номером <?php echo $orderID ?> не найден
<?php else: ?>
    <?php
    if ($order["PAYED"] != "Y") {
        CSaleOrder::PayOrder($order["ID"], "Y");

        $arFields = array(
            "PS_STATUS" => "Y",
            "PS_STATUS_CODE" => 'paid',
            "PS_STATUS_DESCRIPTION" => 'Payment: paid',
            "PS_STATUS_MESSAGE" => 'ok',
            "PS_SUM" => $order['PRICE'],
            "PS_CURRENCY" => $order['CURRENCY'],
            "PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
        );

        $logs['status_fields'] = $arFields;
        CEventLog::Add(array(
            "SEVERITY" => 'INFO',
            "AUDIT_TYPE_ID" => 'Платежный модуль: rbkmoney_payment',
            "MODULE_ID" => 'main',
            "ITEM_ID" => 'notification',
            "DESCRIPTION" => print_r($logs, true),
        ));

        CSaleOrder::Update($orderID, $arFields);
    }

    $orderStatus = ($order["PAYED"] != "Y") ? 'не оплачен' : 'оплачен';
    $statusPageURL = sprintf('%s?ID=%s', GetPagePath('personal/order'), $orderID);
    ?>
    Заказ с номером <?php echo $orderID ?> находится в статусе: "<?php echo $orderStatus; ?>"<br/>
    Подробное состояние заказа можно узнать на <a href="<?php echo $statusPageURL; ?>">странице заказа</a>
<?php endif; ?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
