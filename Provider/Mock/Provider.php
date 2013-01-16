<?php

namespace Astina\Bundle\PaymentBundle\Provider\Mock;

use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;
use Astina\Bundle\PaymentBundle\Provider\ProviderInterface;
use Astina\Bundle\PaymentBundle\Provider\OrderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class Provider implements ProviderInterface
{	
    public function createTransaction(OrderInterface $order = null)
    {
    	$transaction = new Transaction();

        if ($order) {
            $transaction->setAmount($order->getTotalPrice());
        }

        return $transaction;
    }
    
    public function authorizeTransaction(TransactionInterface $transaction)
    {
    }
    
    public function captureTransaction(TransactionInterface $transaction)
    {
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
        if ($transaction->getAmount()) {
            $successUrl .= ((strpos($successUrl, '?') === false) ? '?': '&') . '_mock_amount=' . $transaction->getAmount();
        }

        return $successUrl;
    }

    public function createTransactionFromRequest(Request $request)
    {
    	$transaction = $this->createTransaction();

        if ($amount = $request->get('_mock_amount')) {
            $transaction->setAmount($amount);
        }

        return $transaction;
    }
}