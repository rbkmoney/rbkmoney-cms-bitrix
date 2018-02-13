﻿<?php

/**
 * Class RBKmoney
 */
class RBKmoney
{

    /**
     * HTTP CODE
     */
    const HTTP_CODE_OK = 200;
    const HTTP_CODE_CREATED = 201;
    const HTTP_CODE_MOVED_PERMANENTLY = 301;
    const HTTP_CODE_BAD_REQUEST = 400;
    const HTTP_CODE_INTERNAL_SERVER_ERROR = 500;

    /**
     * Create invoice settings
     */
    const CREATE_INVOICE_TEMPLATE_DUE_DATE = 'Y-m-d\TH:i:s\Z';
    const CREATE_INVOICE_DUE_DATE = '+1 days';


    /**
     * Constants for Callback
     */
    const SIGNATURE = 'HTTP_CONTENT_SIGNATURE';


    const EVENT_TYPE = 'eventType';

    // EVENT TYPE INVOICE
    const EVENT_TYPE_INVOICE_CREATED = 'InvoiceCreated';
    const EVENT_TYPE_INVOICE_PAID = 'InvoicePaid';
    const EVENT_TYPE_INVOICE_CANCELLED = 'InvoiceCancelled';
    const EVENT_TYPE_INVOICE_FULFILLED = 'InvoiceFulfilled';

    // EVENT TYPE PAYMENT
    const EVENT_TYPE_PAYMENT_STARTED = 'PaymentStarted';
    const EVENT_TYPE_PAYMENT_PROCESSED = 'PaymentProcessed';
    const EVENT_TYPE_PAYMENT_CAPTURED = 'PaymentCaptured';
    const EVENT_TYPE_PAYMENT_CANCELLED = 'PaymentCancelled';
    const EVENT_TYPE_PAYMENT_FAILED = 'PaymentFailed';

    const INVOICE = 'invoice';
    const INVOICE_ID = 'id';
    const INVOICE_SHOP_ID = 'shopID';
    const INVOICE_METADATA = 'metadata';
    const INVOICE_STATUS = 'status';
    const INVOICE_AMOUNT = 'amount';

    const ORDER_ID = 'order_id';

    private $api_url = 'https://api.rbk.money/v1/';

    private $merchant_private_key = '';
    private $shop_id = '';
    private $currency = '';
    private $product = '';
    private $description = '';
    private $amount = 0;
    private $order_id;

    protected $errors = array();

    protected $requiredFields = array();

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->api_url;
    }

    /**
     * @param string $api_url
     */
    public function setApiUrl($api_url)
    {
        if (filter_var($api_url, FILTER_VALIDATE_URL) === false) {
            $this->setErrors($api_url . ' is not a valid URL');
        }

        $this->api_url = $api_url;
    }

    /**
     * @return string
     */
    public function getMerchantPrivateKey()
    {
        return $this->merchant_private_key;
    }

    /**
     * @param string $merchant_private_key
     */
    public function setMerchantPrivateKey($merchant_private_key)
    {
        $this->merchant_private_key = $merchant_private_key;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shop_id;
    }

    /**
     * @param string $shop_id
     */
    public function setShopId($shop_id)
    {
        $this->shop_id = $shop_id;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $amount
     * @throws RBKmoneyException
     */
    public function setAmount($amount)
    {
        if(!is_numeric($amount)) {
            throw new RBKmoneyException($amount . ' not a numeric');
        }

        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @param mixed $order_id
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }


    /**
     * Получаем данные об ошибках
     * @return array
     */
    private function getErrors()
    {
        return $this->errors;
    }

    /**
     * Сохраняем данные об ошибках
     * @param array $errors
     */
    private function setErrors($errors)
    {
        $this->errors[] = $errors;
    }

    /**
     * Очистка стека ошибок
     */
    private function clearErrors()
    {
        $this->errors = array();
    }

    /**
     * Возвращает массив обязательных полей
     * @return array
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }

    public function setRequiredFields($requiredFields)
    {
        if (!is_array($requiredFields) || empty($requiredFields)) {
            $this->setErrors('Отсутствуют обязательные поля');
        }
        $this->requiredFields = $requiredFields;
    }

    public function __construct(array $params = array())
    {
        if (!empty($params)) {
            $this->bind($params);
        }
    }

    private function toUpper($pockets)
    {
        return ucfirst(str_replace(['_', '-'], '', $pockets[1]));
    }

    private function getMethodName($name, $prefix = 'get')
    {
        $key = preg_replace_callback('{([_|-]\w)}s', array(__CLASS__, 'toUpper'), $name);
        return $prefix . ucfirst($key);
    }

    private function bind(array $params)
    {
        foreach ($params as $name => $value) {
            $method = $this->getMethodName($name, 'set');
            if (!empty($value) && method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    private function checkRequiredFields()
    {
        $required_fields = $this->getRequiredFields();
        foreach ($required_fields as $field) {
            $method = $this->getMethodName($field);
            if (method_exists($this, $method)) {
                $value = $this->$method();
                if (empty($value)) $this->setErrors('<b>' . $field . '</b> is required');
            } else {
                $this->setErrors($field . ' method not found');
            }
        }
    }

    /**
     * Prepare due date
     *
     * @return string
     */
    private function prepare_due_date()
    {
        date_default_timezone_set('UTC');
        return date(static::CREATE_INVOICE_TEMPLATE_DUE_DATE, strtotime(static::CREATE_INVOICE_DUE_DATE));
    }

    /**
     * Prepare metadata
     *
     * @param $order_id
     * @return array
     */
    private function prepare_metadata($order_id)
    {
        return array(
            'cms' => 'bitrix',
            'cms_version' => '17.5.7',
            'module' => 'rbkmoney_checkout',
            'order_id' => $order_id,
        );
    }

    /**
     * Prepare amount (e.g. 124.24 -> 12424)
     *
     * @param $amount int
     * @return int
     */
    private function prepare_amount($amount)
    {
        return $amount * 100;
    }

    private function prepare_api_url($path = '', $query_params = array())
    {
        $url = rtrim($this->api_url, '/') . '/' . $path;
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        return $url;
    }

    public function create_invoice()
    {
        $this->setRequiredFields(array(
            'merchant_private_key',
            'shop_id',
            'amount',
            'order_id',
            'currency',
            'product',
            'description',
        ));

        $data = array(
            'shopID' => $this->shop_id,
            'amount' => $this->prepare_amount($this->amount),
            'metadata' => $this->prepare_metadata($this->order_id),
            'dueDate' => $this->prepare_due_date(),
            'currency' => $this->currency,
            'product' => $this->product,
            'description' => $this->description,
        );

        $this->validate();
        $url = $this->prepare_api_url('processing/invoices');
        return $this->send($url, $this->getHeaders(), json_encode($data, true), 'init_invoice');
    }

    private function send($url, $headers = array(), $data = '', $type = '')
    {
        $logs = array(
            'request' => array(
                'url' => $url,
                'method' => 'POST',
                'headers' => $headers,
                'data' => $data,
            ),
        );
        RBKmoneyLogger::loggerInfo($type . ': request', $logs);

        if (empty($url)) {
            throw new RBKmoneyException('Не передан обязательный параметр url');
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $curl_errno = curl_errno($curl);

        $response['http_code'] = $info['http_code'];

        if ($response['http_code'] != static::HTTP_CODE_CREATED) {
            $message = 'An error occurred while creating invoice';
            throw new RBKmoneyException($message);
        }

        $response['body'] = $body;
        $response['error'] = $curl_errno;

        $logs['response'] = $response;
        RBKmoneyLogger::loggerInfo($type . ': response', $logs);

        curl_close($curl);

        return json_decode($response['body'], true);
    }

    private function getHeaders() {
        $headers = array();
        $headers[] = 'X-Request-ID: ' . uniqid();
        $headers[] = 'Authorization: Bearer ' . $this->merchant_private_key;
        $headers[] = 'Content-type: application/json; charset=utf-8';
        $headers[] = 'Accept: application/json';
        return $headers;
    }

    private function validate() {
        $this->checkRequiredFields();

        if (count($this->getErrors()) > 0) {
            $errors = 'Errors found: ' . implode(', ', $this->getErrors());
            RBKmoneyLogger::loggerError(__CLASS__, $errors);
            throw new RBKmoneyException($errors);
        }

        $this->clearErrors();
    }

}
