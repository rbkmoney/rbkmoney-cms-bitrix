<?php


class RBKmoneyLogger
{
    const AUDIT_TYPE_ID = 'Платежный модуль: rbkmoney_payment';
    const MODULE_ID = 'main';

    const SEVERITY_ERROR = 'ERROR';
    const SEVERITY_SECURITY = 'SECURITY';
    const SEVERITY_WARNING = 'WARNING';
    const SEVERITY_INFO = 'INFO';
    const SEVERITY_DEBUG = 'DEBUG';

    public static function loggerInfo($item_id, $description)
    {
        static::logger(
            static::SEVERITY_INFO,
            static::AUDIT_TYPE_ID,
            static::MODULE_ID,
            $item_id,
            $description
        );
    }

    public static function loggerError($item_id, $description)
    {
        static::logger(
            static::SEVERITY_ERROR,
            static::AUDIT_TYPE_ID,
            static::MODULE_ID,
            $item_id,
            $description
        );
    }

    public static function logger($severity, $audit_type_id, $module_id, $item_id, $description)
    {
        CEventLog::Add(array(
            "SEVERITY" => $severity,
            "AUDIT_TYPE_ID" => $audit_type_id,
            "MODULE_ID" => $module_id,
            "ITEM_ID" => $item_id,
            "DESCRIPTION" => print_r($description, true),
        ));
    }

}