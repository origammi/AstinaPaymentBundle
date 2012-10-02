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

    public function verifyPayConfirm($data, $signature);

    public function createPayComplete($transactionId);
}
