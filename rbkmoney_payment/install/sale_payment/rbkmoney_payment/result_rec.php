<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('sale');
?>

<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php

include(dirname(__FILE__) . "/sdk/rbkmoney_autoload.php");

$body = file_get_contents('php://input');

$logs = array(
    'request' => array(
        'method' => 'POST',
        'data' => $body,
    ),
);

CEventLog::Add(array(
    "SEVERITY" => 'INFO',
    "AUDIT_TYPE_ID" => 'Платежный модуль: rbkmoney_payment',
    "MODULE_ID" => 'main',
    "ITEM_ID" => 'notification',
    "DESCRIPTION" => print_r($logs, true),
));

if (empty($_SERVER[RBKmoney::SIGNATURE])) {
    $logs['error'] = array(
        'message' => 'Сигнатура отсутствует',
    );
    CEventLog::Add(array(
        "SEVERITY" => 'ERROR',
        "AUDIT_TYPE_ID" => 'Платежный модуль: rbkmoney_payment',
        "MODULE_ID" => 'main',
        "ITEM_ID" => 'notification',
        "DESCRIPTION" => print_r($logs, true),
    ));
    http_response_code(400);
    exit();
}

$required_fields = array(
    RBKmoney::INVOICE_ID,
    RBKmoney::PAYMENT_ID,
    RBKmoney::AMOUNT,
    RBKmoney::CURRENCY,
    RBKmoney::CREATED_AT,
    RBKmoney::METADATA,
    RBKmoney::STATUS
);
$data = json_decode($body, TRUE);
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        $logs['error'] = array(
            'message' => 'Отсутствует обязательное поле: ' . $field,
        );
        CEventLog::Add(array(
            "SEVERITY" => 'ERROR',
            "AUDIT_TYPE_ID" => 'Платежный модуль: rbkmoney_payment',
            "MODULE_ID" => 'main',
            "ITEM_ID" => 'notification',
            "DESCRIPTION" => print_r($logs, true),
        ));
        http_response_code(400);
        exit();
    }
}

if (empty($data[RBKmoney::METADATA][RBKmoney::ORDER_ID])) {
    $logs['error'] = array(
        'message' => 'Отсутствует номер заказа',
    );
    CEventLog::Add(array(
        "SEVERITY" => 'ERROR',
        "AUDIT_TYPE_ID" => 'Платежный модуль: rbkmoney_payment',
        "MODULE_ID" => 'main',
        "ITEM_ID" => 'notification',
        "DESCRIPTION" => print_r($logs, true),
    ));
    http_response_code(400);
    exit();
}
if (!$signature = base64_decode($_SERVER[RBKmoney::SIGNATURE])) {
    http_response_code(400);
    exit();
}
$public_key = CSalePaySystemAction::GetParamValue("SALE_RBKMONEY_MERCHANT_CALLBACK_PUBLIC_KEY");
if (!RBKmoneyVerification::signature($body, $signature, $public_key)) {
    $logs['error'] = array(
        'message' => 'Сигнатура не совпадает',
    );
    CEventLog::Add(array(
        "SEVERITY" => 'ERROR',
        "AUDIT_TYPE_ID" => 'Платежный модуль: rbkmoney_payment',
        "MODULE_ID" => 'main',
        "ITEM_ID" => 'notification',
        "DESCRIPTION" => print_r($logs, true),
    ));
    http_response_code(400);
    exit();
}

$orderId = $data[RBKmoney::METADATA][RBKmoney::ORDER_ID];
if (!($arOrder = CSaleOrder::GetByID($orderId))) {
    $logs['error'] = array(
        'message' => 'Заказ ' . $orderId . ' не найден',
    );
    CEventLog::Add(array(
        "SEVERITY" => 'ERROR',
        "AUDIT_TYPE_ID" => 'Платежный модуль: rbkmoney_payment',
        "MODULE_ID" => 'main',
        "ITEM_ID" => 'notification',
        "DESCRIPTION" => print_r($logs, true),
    ));
    http_response_code(400);
    exit();
}

if ($arOrder["PAYED"] == "Y") {
    $logs['error'] = array(
        'message' => 'Заказ ' . $orderId . ' уже оплачен',
    );
    CEventLog::Add(array(
        "SEVERITY" => 'ERROR',
        "AUDIT_TYPE_ID" => 'Платежный модуль: rbkmoney_payment',
        "MODULE_ID" => 'main',
        "ITEM_ID" => 'notification',
        "DESCRIPTION" => print_r($logs, true),
    ));
    http_response_code(400);
    exit();
}

if ($arOrder["PAYED"] != "Y" && $data[RBKmoney::STATUS] == "paid") {
    $logs['order_payment'] = "Заказ оплачен";
    CSaleOrder::PayOrder($arOrder["ID"], "Y");
}

$arFields = array(
    "PS_STATUS" => "Y",
    "PS_STATUS_CODE" => $data[RBKmoney::STATUS],
    "PS_STATUS_DESCRIPTION" => $body,
    "PS_STATUS_MESSAGE" => 'ok',
    "PS_SUM" => $data[RBKmoney::AMOUNT],
    "PS_CURRENCY" => $data[RBKmoney::CURRENCY],
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

CSaleOrder::Update($arOrder["ID"], $arFields);

?>
