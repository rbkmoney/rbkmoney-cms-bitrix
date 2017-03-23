<?
IncludeModuleLangFile(__FILE__);

/**
 * RBKmoney Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @version         1.0
 * @author          RBKmoney
 * @copyright       Copyright (c) 2017 RBKmoney
 * @license         http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * EXTENSION INFORMATION
 *
 * 1C-Bitrix        17.0
 * RBKmoney DOCS     https://rbkmoney.github.io/docs/
 *
 */
Class rbkmoney_payment extends CModule
{
    var $MODULE_ID = 'rbkmoney_payment';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = trim($arModuleVersion['VERSION']);
            $this->MODULE_VERSION_DATE = trim($arModuleVersion['VERSION_DATE']);
        }
        $this->MODULE_NAME = trim(GetMessage('RBKMONEY_MODULE_NAME'));
        $this->MODULE_DESCRIPTION = trim(GetMessage('RBKMONEY_MODULE_DESC'));
        $this->PARTNER_NAME = trim(GetMessage('RBKMONEY_PARTNER_NAME'));
        $this->PARTNER_URI = trim(GetMessage('RBKMONEY_PARTNER_URI'));
    }

    function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/notifications',
            $_SERVER['DOCUMENT_ROOT'] . '/personal/order/' . $this->MODULE_ID
        );
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/sale_payment/' . $this->MODULE_ID,
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/payment/' . $this->MODULE_ID,
            true, true
        );
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx('bitrix/modules/sale/payment/' . $this->MODULE_ID);
        DeleteDirFilesEx('personal/order/' . $this->MODULE_ID);
        return true;
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->InstallFiles();
        RegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage('RBKMONEY_INSTALL_TITLE'), $DOCUMENT_ROOT . '/bitrix/modules/' . $this->MODULE_ID . '/install/step.php');
        return true;
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(GetMessage('RBKMONEY_UNINSTALL_TITLE'), $DOCUMENT_ROOT . '/bitrix/modules/' . $this->MODULE_ID . '/install/unstep.php');
        return true;
    }

}
