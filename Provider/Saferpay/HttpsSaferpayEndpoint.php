<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\PaymentException;
use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;

class HttpsSaferpayEndpoint implements SaferpayEndpoint
{
    const SAFERPAY_BASE_URL = 'https://www.saferpay.com/hosting/';
    const SAFERPAY_TEST_URL = 'https://test.saferpay.com/hosting/';

    /** @var string $accountId */
    private $accountId;

    /** @var string $password */
    private $password;

    /** @var string $testmode */
    private $testmode;

    /** @var string $vtConfig */
    private $vtConfig;

    private $logger;

    public function __construct($logger,
                                $accountId,
                                $password = null,
                                $testmode,
                                $vtConfig = null)
    {
        $this->logger = $logger;
        $this->accountId = $accountId;
        $this->password = $password;
        $this->testmode = $testmode;
        $this->vtConfig = $vtConfig;
    }

    /**
     * @param $transaction
     * @param $successUrl
     * @param $errorUrl
     * @param $cancelUrl
     * @param $params
     * @return string
     */
    public function retrievePaymentLink(TransactionInterface $transaction, $successUrl, $errorUrl, $cancelUrl, $params)
    {
        $paymentInitParams = $this->generatePaymentInitParams($transaction, $successUrl, $errorUrl, $cancelUrl, $params);

        return $this->apiCall('CreatePayInit.asp', $paymentInitParams);
    }

    public function verifyPayConfirm($data, $signature)
    {
        $payConfirmParams = array();
        $payConfirmParams['DATA'] = $data;
        $payConfirmParams['SIGNATURE'] = $signature;

        return $this->apiCall('VerifyPayConfirm.asp', $payConfirmParams);
    }

    public function createPayComplete($transactionId)
    {
        $payCompleteParams = array();
        $payCompleteParams['ACCOUNTID'] = $this->accountId;
        $payCompleteParams['ID'] = $transactionId;

        if($this->password) {
            $payCompleteParams['spPassword'] = $this->password;
        }

        return $this->apiCall('PayCompleteV2.asp', $payCompleteParams);
    }

    public function generatePaymentInitParams(TransactionInterface $transaction,
                                              $successUrl = null,
                                              $errorUrl = null,
                                              $cancelUrl = null,
                                              array $params = array())
    {
        $paymentInitParams = array();

        $paymentInitParams['ACCOUNTID'] = $this->accountId;
        $paymentInitParams['AMOUNT'] = $transaction->getAmount(); //amount in minor currency unit
        $paymentInitParams['CURRENCY'] = $transaction->getCurrency();
        $paymentInitParams['ORDERID'] = $transaction->getReference();
        $paymentInitParams['DESCRIPTION'] = $transaction->getDescription();
        $paymentInitParams['VTCONFIG'] = $this->vtConfig;

        $paymentInitParams['SUCCESSLINK'] = $successUrl;
        $paymentInitParams['FAILLINK'] = $errorUrl;
        $paymentInitParams['BACKLINK'] = $cancelUrl;

        foreach($params as $key => $value) {
            $paymentInitParams[$key] = $value;
        }

        return $paymentInitParams;
    }

    private function apiCall($page, $params)
    {
        $curl = curl_init();

        $data = array();
        foreach ($params as $name => &$value) {
            $data[] = sprintf('%s=%s', urlencode($name), urlencode($value));
        }

        if ($this->testmode == true) {
            $url = self::SAFERPAY_TEST_URL . $page . '?' . implode('&', $data);
        }
        else {
            $url = self::SAFERPAY_BASE_URL . $page . '?' . implode('&', $data);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);

        $this->logger->debug('Sending Saferpay API request: ' . $page, $data);

        //getting response from server
        $response = curl_exec($curl);

        if (!$response) {
            throw new PaymentException('Saferpay API unreachable');
        }

        if (strpos($response, 'ERROR:') === 0) {
            throw new PaymentException($response);
        }

        return $response;
    }
}
