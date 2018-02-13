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

    public static function loggerInfoWithOutput($item_id, $description, $message, $http_code = RBKmoney::HTTP_CODE_OK)
    {
        RBKmoneyLogger::loggerInfo($item_id, $description);
        http_response_code($http_code);
        echo json_encode(array('message' => $message));
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

    public static function loggerErrorWithOutput($item_id, $description, $message, $http_code = RBKmoney::HTTP_CODE_BAD_REQUEST)
    {
        RBKmoneyLogger::loggerError($item_id, $description);
        http_response_code($http_code);
        echo json_encode(array('message' => $message));
        exit();
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