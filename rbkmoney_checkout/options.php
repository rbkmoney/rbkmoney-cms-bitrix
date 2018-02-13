<?
$module_id = 'rbkmoney_checkout';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/include.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/options.php');
include_once 'CModuleOptions.php';

$showRightsTab = true;

$arTabs = array(
   array(
      'DIV' => 'edit1',
      'TAB' => 'Настройки',
      'ICON' => '',
      'TITLE' => 'Настройки'
   )
);

$arGroups = array(
   'MAIN' => array('TITLE' => 'Основные настройки', 'TAB' => 0)
);

$arOptions = array(
    
    'SHOP_ID' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("rbkmoney_checkout_SHOP_ID"),
      'TYPE' => 'STRING',
      'DEFAULT' => 'TEST',
      'SORT' => '0',
      'NOTES' => GetMessage("rbkmoney_checkout_DESCRIPTION_SHOP_ID")
   ),
        
   'MERCHANT_PRIVATE_KEY' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("rbkmoney_checkout_MERCHANT_PRIVATE_KEY"),
      'TYPE' => 'TEXT',
      'DEFAULT' => '',
      'SORT' => '1',
      'COLS' => 50,
      'ROWS' => 5,
      'NOTES' => GetMessage("rbkmoney_checkout_DESCRIPTION_MERCHANT_PRIVATE_KEY")
   ),
    
   'MERCHANT_CALLBACK_PUBLIC_KEY' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("rbkmoney_checkout_MERCHANT_CALLBACK_PUBLIC_KEY"),
      'TYPE' => 'TEXT',
      'DEFAULT' => '',
      'SORT' => '2',
      'COLS' => 50,
      'ROWS' => 5,
      'NOTES' => GetMessage("rbkmoney_checkout_DESCRIPTION_MERCHANT_CALLBACK_PUBLIC_KEY")
   ),

);

$opt = new CModuleOptions($module_id, $arTabs, $arGroups, $arOptions, $showRightsTab);
$opt->ShowHTML();

?>
