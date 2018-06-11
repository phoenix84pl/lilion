<?php
namespace InPay;

define("INPAY_API_URL", "https://api.inpay.pl");
define("INPAY_API_URL_TEST", "https://api.test-inpay.pl");

class inpay
{
    private $availableCurrencies = array("PLN", "USD", "EUR");
    private $apiUrl = INPAY_API_URL;
    private $apiKey = "";
    private $apiKeySecret = "";
    private $currency = "PLN";
    private $customerEmail;
    private $callbackUrl;
    private $successUrl;
    private $failUrl;

    public function init($options)
    {
        foreach((array) $options as $key => $val)
        {
            $this->$key = $val;
        }
    }

    public function invoiceCreate($amount, $orderCode=null)
    {
		if(!is_numeric($amount)) return false;

        $data = array(
            'apiKey' => $this->apiKey,
            'amount' => number_format(str_replace(",",".", $amount), 2),
            'currency' => $this->currency,
            'orderCode' => $orderCode,
            'customerEmail' => $this->customerEmail,
            'callbackUrl' => $this->callbackUrl,
            'successUrl' => $this->successUrl,
            'failUrl' => $this->failUrl
        );
        return $this->requestPost("/invoice/create", $data);
    }

    public function invoiceStatus($invoiceCode) {
        $data = array(
            'invoiceCode' => $invoiceCode,
        );
        return $this->requestPost("/invoice/status", $data);
    }
    public function TestMode($enable) {
        $this->apiUrl = (TRUE === $enable) ? INPAY_API_URL_TEST : INPAY_API_URL;
    }
    public function setUrls($callbackUrl, $successUrl, $failUrl) {
            $this->callbackUrl = $callbackUrl;
            $this->successUrl = $successUrl;
            $this->failUrl = $failUrl;
    }
    public function setCustomerEmail($email) {
            $this->customerEmail = $email;
    }
    public function setCurrency($name) {
        if(in_array($name, $this->availableCurrencies)) {
            $this->currency = $name;
        } else {
            throw new Exception("Currency unavailable");
        }
    }
    public function callback() {
        $receivedCallback = isset($_POST['orderCode']) &&
            isset($_POST['amount']) &&
            isset($_POST['invoiceCode']) &&
            isset($_POST['fee']) &&
            isset($_POST['status']);
        if(!$receivedCallback) {
            return FALSE;
        }
        if(!$this->checkApiHash()) {
            throw new Exception("singature hash doesn't match!");
        }
        return array(
            'invoice_id' => $_POST['orderCode'],
            'in' => $_POST['amount'],
            'description' => 'Thank you! Transaction was successful!',
            'fee' => $_POST['fee'],
            'transaction_id' => $_POST['invoiceCode']
        );
    }
    /*
     * private methods, don't use directly
     */
    private function requestPost($method, $data) {
        $url = $this->apiUrl . $method;
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result, true);
    }
    private function checkApiHash() {
        $apiHash = $_SERVER['HTTP_API_HASH'];
        $query = http_build_query($_POST);
        $hash = hash_hmac("sha512", $query, $this->apiKeySecret);
        // Timing attack safe string comparison
        return hash_equals($apiHash, $hash);
    }
}