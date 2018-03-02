<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('sale');
?>

<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php
include(dirname(__FILE__) . "/sdk/rbkmoney_autoload.php");

$content = file_get_contents('php://input');

$logs = array(
    'request' => array(
        'method' => 'POST',
        'data' => $content,
    ),
);

$item_id = 'notification';

RBKmoneyLogger::loggerInfo($item_id, $logs);

if (empty($_SERVER[RBKmoney::SIGNATURE])) {
    $logs['error']['message'] = 'Webhook notification signature missing';
    RBKmoneyLogger::loggerErrorWithOutput($item_id, $logs, $logs['error']['message']);
}

$logs['signature'] = $_SERVER[RBKmoney::SIGNATURE];
$params_signature = RBKmoneyVerification::get_parameters_content_signature($_SERVER[RBKmoney::SIGNATURE]);

if (empty($params_signature[RBKmoneyVerification::SIGNATURE_ALG])) {
    $logs['error']['message'] = 'Missing required parameter ' . RBKmoneyVerification::SIGNATURE_ALG;
    RBKmoneyLogger::loggerErrorWithOutput($item_id, $logs, $logs['error']['message']);
}
if (empty($params_signature[RBKmoneyVerification::SIGNATURE_DIGEST])) {
    $logs['error']['message'] = 'Missing required parameter ' . RBKmoneyVerification::SIGNATURE_DIGEST;
    RBKmoneyLogger::loggerErrorWithOutput($item_id, $logs, $logs['error']['message']);
}

$signature = RBKmoneyVerification::url_safe_b64decode($params_signature[RBKmoneyVerification::SIGNATURE_DIGEST]);
$public_key = RBKmoneyVerification::prepare_public_key(COption::GetOptionString("rbkmoney_checkout", "MERCHANT_CALLBACK_PUBLIC_KEY"));
$logs['public_key'] = $public_key;

if (!RBKmoneyVerification::verification_signature($content, $signature, $public_key)) {
    $logs['error']['message'] = 'Webhook notification signature mismatch';
    RBKmoneyLogger::loggerErrorWithOutput($item_id, $logs, $logs['error']['message']);
}

$required_fields = [RBKmoney::INVOICE, RBKmoney::EVENT_TYPE];
$data = json_decode($content, TRUE);
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        $logs['error']['message'] = 'One or more required fields are missing';
        RBKmoneyLogger::loggerErrorWithOutput($item_id, $logs, $logs['error']['message']);
    }
}

if (empty($data[RBKmoney::INVOICE][RBKmoney::INVOICE_SHOP_ID])) {
    $message = RBKmoney::INVOICE_SHOP_ID . ' is missing';
    $this->output($message, $logs);
}
if (empty($data[RBKmoney::INVOICE][RBKmoney::INVOICE_METADATA][RBKmoney::ORDER_ID])) {
    $message = RBKmoney::ORDER_ID . ' is missing';
    $this->output($message, $logs);
}

$order_id = $data[RBKmoney::INVOICE][RBKmoney::INVOICE_METADATA][RBKmoney::ORDER_ID];
if (!($arOrder = CSaleOrder::GetByID($order_id))) {
    $logs['error']['message'] = 'Order ' . $order_id . ' not found';
    RBKmoneyLogger::loggerErrorWithOutput($item_id, $logs, $logs['error']['message']);
}

if ($arOrder["PAYED"] == "Y") {
    $logs['error']['message'] = 'Order ' . $order_id . ' already paid';
    RBKmoneyLogger::loggerErrorWithOutput($item_id, $logs, $logs['error']['message']);
}

if ($arOrder["PAYED"] != "Y" && $data[RBKmoney::INVOICE][RBKmoney::INVOICE_STATUS] == "paid") {
    $logs['order_payment'] = "Order paid";
    CSaleOrder::PayOrder($arOrder["ID"], "Y");
}

$arFields = array(
    "PS_STATUS" => "Y",
    "PS_STATUS_CODE" => $data[RBKmoney::INVOICE_STATUS],
    "PS_STATUS_DESCRIPTION" => $content,
    "PS_STATUS_MESSAGE" => 'ok',
    "PS_SUM" => $data[RBKmoney::AMOUNT],
    "PS_CURRENCY" => $data[RBKmoney::CURRENCY],
    "PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
);

CSaleOrder::Update($arOrder["ID"], $arFields);

$logs['status_fields'] = $arFields;
RBKmoneyLogger::loggerInfoWithOutput($item_id, $logs, 'OK');

?>
