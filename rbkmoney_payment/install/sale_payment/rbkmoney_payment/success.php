<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

CModule::IncludeModule('sale');

$APPLICATION->SetPageProperty("title", "Информация о заказе");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Оплата заказа");

?>

Платеж принят, заказ обрабатывается

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
