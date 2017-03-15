<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?php
include(GetLangFileName(dirname(__FILE__)."/", "/.description.php"));

$psTitle = "RBKmoney";
$psDescription = "<a href=\"https://rbkmoney.github.io/docs/\" target=\"_blank\">https://rbkmoney.github.io/docs</a>";
$arPSCorrespondence = array(

    "SHOULD_PAY" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_SHOULD_PAY"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_SHOULD_PAY"),
        "VALUE" => "SHOULD_PAY",
        "TYPE" => "ORDER"
    ),
    "CURRENCY" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_CURRENCY"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_CURRENCY"),
        "VALUE" => "CURRENCY",
        "TYPE" => "ORDER"
    ),
    "ORDER_ID" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_ORDER_ID"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_ORDER_ID"),
        "VALUE" => "ID",
        "TYPE" => "ORDER"
    ),
    "FORM_PATH_IMG_LOGO" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_FORM_PATH_IMG_LOGO"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_FORM_COMPANY_NAME"),
        "VALUE" => "https://checkout.rbk.money/checkout/images/logo.png",
        "TYPE" => ""
    ),
    "FORM_COMPANY_NAME" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_FORM_COMPANY_NAME"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_FORM_PATH_IMG_LOGO"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "MERCHANT_CALLBACK_PUBLIC_KEY" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_MERCHANT_CALLBACK_PUBLIC_KEY"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_MERCHANT_CALLBACK_PUBLIC_KEY"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "MERCHANT_PRIVATE_KEY" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_MERCHANT_PRIVATE_KEY"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_MERCHANT_PRIVATE_KEY"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "SHOP_ID" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_SHOP_ID"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_SHOP_ID"),
        "VALUE" => "0000000",
        "TYPE" => ""
    ),
);
?>
