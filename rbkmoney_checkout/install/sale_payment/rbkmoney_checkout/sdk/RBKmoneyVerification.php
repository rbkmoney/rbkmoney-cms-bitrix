﻿<?php

/**
 * Class RBKmoneyVerification
 */
class RBKmoneyVerification
{
    /**
     * Openssl verify
     */
    const OPENSSL_VERIFY_SIGNATURE_IS_CORRECT = 1;
    const OPENSSL_VERIFY_SIGNATURE_IS_INCORRECT = 0;
    const OPENSSL_VERIFY_ERROR = -1;

    const SIGNATURE_ALG = 'alg';
    const SIGNATURE_DIGEST = 'digest';
    const SIGNATURE_PATTERN = "|alg=(\S+);\sdigest=(.*)|i";

    public static function verification_signature($data = '', $signature = '', $public_key = '')
    {
        if (empty($data) || empty($signature) || empty($public_key)) {
            return FALSE;
        }
        $public_key_id = openssl_get_publickey($public_key);
        if (empty($public_key_id)) {
            return FALSE;
        }
        $verify = openssl_verify($data, $signature, $public_key_id, OPENSSL_ALGO_SHA256);
        return ($verify == static::OPENSSL_VERIFY_SIGNATURE_IS_CORRECT);
    }

    public static function url_safe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public static function get_parameters_content_signature($content_signature)
    {
        preg_match_all(static::SIGNATURE_PATTERN, $content_signature, $matches, PREG_PATTERN_ORDER);
        $params = array();
        $params[static::SIGNATURE_ALG] = !empty($matches[1][0]) ? $matches[1][0] : '';
        $params[static::SIGNATURE_DIGEST] = !empty($matches[2][0]) ? $matches[2][0] : '';
        return $params;
    }

    public static function prepare_public_key($public_key) {
        $public_key_wrap = wordwrap(trim($public_key), 55, PHP_EOL, true);
        return '-----BEGIN PUBLIC KEY-----' . PHP_EOL . $public_key_wrap . PHP_EOL . '-----END PUBLIC KEY-----';
    }

}

?>

