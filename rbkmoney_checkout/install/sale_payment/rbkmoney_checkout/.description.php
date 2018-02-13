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
    "FORM_COMPANY_NAME" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_FORM_COMPANY_NAME"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_FORM_COMPANY_NAME"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "FORM_BUTTON_LABEL" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_FORM_BUTTON_LABEL"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_FORM_BUTTON_LABEL"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "FORM_PRODUCT_DESCRIPTION" => array(
        "NAME" => GetMessage("SALE_RBKMONEY_FORM_PRODUCT_DESCRIPTION"),
        "DESCR" => GetMessage("SALE_RBKMONEY_DESCRIPTION_FORM_PRODUCT_DESCRIPTION"),
        "VALUE" => "",
        "TYPE" => ""
    ),
);
?>
