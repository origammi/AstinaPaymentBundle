<?php

namespace Astina\Bundle\PaymentBundle\Provider\Computop;

use Astina\Bundle\PaymentBundle\Provider\ProviderInterface;
use Astina\Bundle\PaymentBundle\Provider\OrderInterface;
use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;

use Symfony\Component\HttpFoundation\Request;

class Provider implements ProviderInterface
{
    private $creditCardUrl = 'https://www.netkauf.de/paygate/payssl.aspx';

    private $bankTransferUrl = 'https://www.netkauf.de/paygate/payelv.aspx';

    private $merchantId;

    private $password;

    private $hMacKey;

    private $testingMode;

    public function __construct($merchantId, $password, $hMacKey, $testingMode = false)
    {
        $this->merchantId = $merchantId;
        $this->password = $password;
        $this->hMacKey = $hMacKey;
        $this->testingMode = $testingMode;
    }

    /**
     * @param \Astina\Bundle\PaymentBundle\Provider\OrderInterface $order
     * @return \Astina\Bundle\PaymentBundle\Provider\TransactionInterface
     */
    function createTransaction(OrderInterface $order = null)
    {
        $transaction = new Transaction();

        if ($order) {
            $transaction->setCurrency($order->getCurrency());
            $transaction->setAmount((int) $order->getTotalPrice());
        }

        return $transaction;
    }

    /**
     * @param \Astina\Bundle\PaymentBundle\Provider\TransactionInterface $transaction
     */
    function authorizeTransaction(TransactionInterface $transaction)
    {
        // TODO: Implement authorizeTransaction() method.
    }

    /**
     * @param TransactionInterface $transaction
     */
    function captureTransaction(TransactionInterface $transaction)
    {
        // TODO: Implement captureTransaction() method.
    }

    /**
     * @param TransactionInterface $transaction
     * @param string $successUrl
     * @param string $errorUrl
     * @param string $cancelUrl
     * @param array $params
     * @return string
     */
    function createPaymentUrl(TransactionInterface $transaction, $successUrl = null, $errorUrl = null, $cancelUrl = null, array $params = array())
    {
        $params = array_merge(array(
            'MerchantID' => $this->merchantId,
            'Response' => 'encrypt',
            'Currency' => $transaction->getCurrency(),
            'Capture' => 'MANUAL',
            'TransID' => $transaction->getReference(),
            'Amount' => $transaction->getAmount(),
            'URLSuccess' => $successUrl,
            'URLFailure' => $errorUrl,
        ), $params);

        if (!isset($params['OrderDesc']) && $transaction->getDescription()) {
            $params['OrderDesc'] = $transaction->getDescription();
        }

        $params['MAC'] = $this->createMac($params);

        if ($this->testingMode) {
            $params['amount'] = 100;
        }

//        $paramStr = http_build_query($params); // params cannot be url encoded!
        $paramStr = $this->createParamStr($params);
        $length = strlen($paramStr);

        // encrypt
        $bf = new CtBlowfish();
        $data = $bf->ctEncrypt($paramStr, $length, $this->password);

        $baseUrl = $this->findPaymentBaseUrl($transaction);

        $params = array(
            'MerchantID' => $this->merchantId,
            'Len' => $length,
            'Data' => $data,
        );

        return sprintf('%s?%s', $baseUrl, http_build_query($params));
    }

    private function createMac($params)
    {
        $macParams = array(
            isset($params['PayID']) ? $params['PayID'] : '',
            $params['TransID'],
            $params['MerchantID'],
            $params['Amount'],
            $params['Currency'],
        );

        $macStr = implode('*', $macParams);

//        $key = pack('H*', $this->hMacKey); // convert from hex to binary
        return hash_hmac('sha256', $macStr, $this->hMacKey);
    }

    private function createParamStr($data)
    {
        $elements = array();
        foreach ($data as $name => $value) {
            $elements[] = sprintf('%s=%s', $name, $value);
        }

        return implode('&', $elements);
    }

    private function findPaymentBaseUrl(TransactionInterface $transaction)
    {
        if ($transaction->getPaymentMethod() == 'bank_transfer') {
            return $this->bankTransferUrl;
        }
        return $this->creditCardUrl;
    }

    /**
     * @param Request $request
     * @return \Astina\Bundle\PaymentBundle\Provider\TransactionInterface
     */
    public function createTransactionFromRequest(Request $request)
    {
        $data = $request->get('Data');
        $len = $request->get('Len');

        $payGate = new CtPayGate("");
        $plain = $payGate->ctDecrypt($data, $len, $this->password);

        $params = array();
        parse_str($plain, $params);

        $transaction = $this->createTransaction();
        $transaction->setTransactionId(isset($params['PayID']) ? $params['PayID'] : null);
        $transaction->setTransactionToken(isset($params['XID']) ? $params['XID'] : null);
        $transaction->setReference(isset($params['TransID']) ? $params['TransID'] : null);
        $transaction->setRequestType(isset($params['Type']) ? $params['Type'] : null);
        $transaction->setResponseCode(isset($params['Code']) ? $params['Code'] : null);
        $transaction->setStatus(isset($params['Status']) ? $params['Status'] : null);
        $transaction->setResponseMessage(isset($params['Description']) ? $params['Description'] : null);

        return $transaction;
    }
}
