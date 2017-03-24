<?php

/**
 * Class RBKmoney
 */
class RBKmoney
{
    /**
     * HTTP METHOD
     */
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_GET = 'GET';

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
    const INVOICE_ID = 'invoice_id';
    const PAYMENT_ID = 'payment_id';
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const CREATED_AT = 'created_at';
    const METADATA = 'metadata';
    const STATUS = 'status';
    const SIGNATURE = 'HTTP_X_SIGNATURE';
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
        if(!is_integer($amount)) {
            throw new RBKmoneyException($amount . ' no a integer');
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
        return [
            'cms' => 'bitrix',
            'cms_version' => '17.0.0',
            'module' => 'rbkmoney_payment',
            'order_id' => $order_id,
        ];
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

    private function prepare_api_url($path = '', $query_params = [])
    {
        $url = rtrim($this->api_url, '/') . '/' . $path;
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        return $url;
    }

    public function create_invoice()
    {
        $this->setRequiredFields([
            'merchant_private_key',
            'shop_id',
            'amount',
            'order_id',
            'currency',
            'product',
            'description',
        ]);

        $headers = [];
        $headers[] = 'X-Request-ID: ' . uniqid();
        $headers[] = 'Authorization: Bearer ' . $this->merchant_private_key;
        $headers[] = 'Content-type: application/json; charset=utf-8';
        $headers[] = 'Accept: application/json';

        $data = [
            'shopID' => (int)$this->shop_id,
            'amount' => $this->prepare_amount($this->amount),
            'metadata' => $this->prepare_metadata($this->order_id),
            'dueDate' => $this->prepare_due_date(),
            'currency' => $this->currency,
            'product' => $this->product,
            'description' => $this->description,
        ];

        $this->validate();
        $url = $this->prepare_api_url('processing/invoices');
        return $this->send($url, static::HTTP_METHOD_POST, $headers, json_encode($data, true), 'init_invoice');
    }

    public function create_access_token($invoice_id)
    {
        if (empty($invoice_id)) {
            throw new RBKmoneyException('Не передан обязательный параметр invoice_id');
        }

        $headers = [];
        $headers[] = 'X-Request-ID: ' . uniqid();
        $headers[] = 'Authorization: Bearer ' . $this->merchant_private_key;
        $headers[] = 'Content-type: application/json; charset=utf-8';
        $headers[] = 'Accept: application/json';

        $url = $this->prepare_api_url('processing/invoices/' . $invoice_id . '/access_tokens');
        $response = $this->send($url, static::HTTP_METHOD_POST, $headers, '', 'access_tokens');
        if ($response['http_code'] != static::HTTP_CODE_CREATED) {
            throw new RBKmoneyException('Возникла ошибка при создании токена для инвойса');
        }

        $response_decode = json_decode($response['body'], true);
        $access_token = !empty($response_decode['payload']) ? $response_decode['payload'] : '';

        return $access_token;
    }

    private function send($url, $method, $headers = [], $data = '', $type = '')
    {
        $logs = array(
            'request' => array(
                'url' => $url,
                'method' => $method,
                'headers' => $headers,
                'data' => $data,
            ),
        );

        RBKmoneyLogger::loggerInfo($type . ': request', $logs);

        if (empty($url)) {
            throw new RBKmoneyException('Не передан обязательный параметр url');
        }

        $allowed_methods = [static::HTTP_METHOD_POST, static::HTTP_METHOD_GET];
        if (!in_array($method, $allowed_methods)) {
            RBKmoneyLogger::loggerError(__CLASS__, $logs);
            throw new RBKmoneyException('Unsupported method ' . $method);
        }

        $curl = curl_init($url);
        if ($method == static::HTTP_METHOD_POST) {
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $curl_errno = curl_errno($curl);

        $response['http_code'] = $info['http_code'];
        $response['body'] = $body;
        $response['error'] = $curl_errno;

        $logs['response'] = $response;

        RBKmoneyLogger::loggerInfo($type . ': response', $logs);

        curl_close($curl);

        return $response;
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
