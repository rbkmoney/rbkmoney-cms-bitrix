<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?
include(dirname(__FILE__) . "/sdk/rbkmoney_autoload.php");

$order_id = CSalePaySystemAction::GetParamValue("ORDER_ID");
$shop_id = COption::GetOptionString("rbkmoney_checkout", "SHOP_ID");
$success_url = RBKmoneyUrlHelper::getBaseUrlWithSlash() . 'personal/order/rbkmoney_checkout/success.php';
$failed_url = RBKmoneyUrlHelper::getBaseUrlWithSlash() . 'personal/order/rbkmoney_checkout/failed.php';
$amount = number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, '.', '');
$currency = trim(CSalePaySystemAction::GetParamValue("CURRENCY"));

$form_company_name = trim(CSalePaySystemAction::GetParamValue("FORM_COMPANY_NAME"));
$company_name = !empty($form_company_name) ? 'data-name="' . $form_company_name . '"' : '';

$form_button_label = trim(CSalePaySystemAction::GetParamValue("FORM_BUTTON_LABEL"));
$button_label = !empty($form_button_label) ? 'data-label="' . $form_button_label . '"' : '';

$form_product_description = trim(CSalePaySystemAction::GetParamValue("FORM_PRODUCT_DESCRIPTION"));
$product_description = !empty($form_product_description) ? 'data-description="' . $form_product_description . '"' : '';


$params = array(
    'shop_id' => $shop_id,
    'currency' => $currency,
    'product' => $order_id,
    'description' => 'Order ID ' . $order_id,
    'amount' => $amount,
    'order_id' => $order_id,
    'success_url' => $success_url,
    'failed_url' => $failed_url,
    'merchant_private_key' => trim(COption::GetOptionString("rbkmoney_checkout", "MERCHANT_PRIVATE_KEY")),
);

$invoice_id = $order['PAY_VOUCHER_NUM'];
$invoice_access_token = $_SESSION[$invoice_id . '_access_token'];

try {
    $rbk_api = new RBKmoney($params);
    if (empty($invoice_id)) {
        $response = $rbk_api->create_invoice();
  
        $invoice_id = $response["invoice"]["id"];
        $invoice_access_token = $response["invoiceAccessToken"]["payload"];
        
        $_SESSION[$invoice_id . '_access_token'] = $invoice_access_token;
        
        CSaleOrder::Update($order_id, Array(
            'PAY_VOUCHER_NUM' => $invoice_id,
            'PAY_VOUCHER_DATE' => $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]
        ));
    }

} catch (Exception $ex) {
    $logs = [
        "code" => $ex->getCode(),
        "message" => $ex->getMessage(),
    ];
    RBKmoneyLogger::loggerError("init_invoice_exception", $logs);
}

?>


<form action="<?= trim($success_url); ?>" method="POST">
    <script src="https://checkout.rbk.money/checkout.js" class="rbkmoney-checkout"
           data-invoice-id="<?= trim($invoice_id); ?>"
           data-invoice-access-token="<?= trim($invoice_access_token); ?>"
           <?= trim($company_name); ?> 
           <?= trim($button_label); ?> 
           <?= trim($product_description); ?> 
    >
</script>
</form>
