<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?
include(dirname(__FILE__) . "/sdk/rbkmoney_autoload.php");

$order_id = CSalePaySystemAction::GetParamValue("ORDER_ID");
$shop_id = CSalePaySystemAction::GetParamValue("SHOP_ID");
$success_url = RBKmoneyUrlHelper::getBaseUrlWithSlash() . 'personal/order/rbkmoney_payment/success.php';
$failed_url = RBKmoneyUrlHelper::getBaseUrlWithSlash() . 'personal/order/rbkmoney_payment/failed.php';
$amount = number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, '.', '');
$currency = trim(CSalePaySystemAction::GetParamValue("CURRENCY"));

$params = array(
    'shop_id' => $shop_id,
    'currency' => $currency,
    'product' => $order_id,
    'description' => 'Order ID ' . $order_id,
    'amount' => $amount,
    'order_id' => $order_id,
    'success_url' => $success_url,
    'failed_url' => $failed_url,
    'merchant_private_key' => trim(CSalePaySystemAction::GetParamValue("MERCHANT_PRIVATE_KEY")),
);

$invoiceId = '';
$invoice_access_token = '';

try {
    $rbk_api = new RBKmoney($params);
    if (empty($order['PAY_VOUCHER_NUM'])) {
        $response_create_invoice = $rbk_api->create_invoice();
        $create_invoice_encode = json_decode($response_create_invoice['body'], true);
        $invoiceId = !empty($create_invoice_encode['id']) ? $create_invoice_encode['id'] : '';

        CSaleOrder::Update($order_id, Array(
            'PAY_VOUCHER_NUM' => $invoiceId,
            'PAY_VOUCHER_DATE' => $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]
        ));
    } else {
        $invoiceId = $order['PAY_VOUCHER_NUM'];
    }

    $invoice_access_token = $rbk_api->create_access_token($invoiceId);

} catch (Exception $ex) {
    $logs = [
        "code" => $ex->getCode(),
        "message" => $ex->getMessage(),
    ];
    RBKmoneyLogger::loggerError("init_invoice_exception", $logs);
}

?>

<script src="https://checkout.rbk.money/payframe/payframe.js" class="rbkmoney-checkout"
        data-invoice-id="<?= trim($invoiceId); ?>"
        data-invoice-access-token="<?= trim($invoice_access_token); ?>"
        data-endpoint-success="<?= trim($success_url) ?>"
        data-endpoint-failed="<?= trim($failed_url) ?>"
        data-amount="<?= $amount ?>"
        data-currency="<?= $currency ?>"
        data-name="<?= trim(CSalePaySystemAction::GetParamValue("FORM_COMPANY_NAME")) ?>"
        data-logo="<?= trim(CSalePaySystemAction::GetParamValue("FORM_PATH_IMG_LOGO")) ?>"
>
</script>
