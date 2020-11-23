<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;

interface SaferpayEndpoint
{
    /**
     * @param $paymentInitUrl
     * @return string
     */
    public function retrievePaymentLink(TransactionInterface $transaction, $successUrl, $errorUrl, $cancelUrl, $params);

    public function assertPayment($paymentToken);

    public function createPayComplete($transactionId);

    public function initializePayment(TransactionInterface $transaction,
        $successUrl = null,
        $errorUrl = null,
        $cancelUrl = null,
        array $params = array());
}
