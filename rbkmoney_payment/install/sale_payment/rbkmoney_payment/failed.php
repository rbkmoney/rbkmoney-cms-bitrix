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
    $orderStatus = ($order['PAYED'] != 'Y') ? 'не оплачен' : 'оплачен';
    $statusPageURL = sprintf('%s?ID=%s', GetPagePath('personal/order'), $orderID);
    ?>
    Заказ с номером <?php echo $orderID ?> находится в статусе: "<?php echo $orderStatus['NAME']; ?>"<br/>
    Подробное состояние заказа можно узнать на <a href="<?php echo $statusPageURL; ?>">странице заказа</a>
<?php endif; ?>

<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
