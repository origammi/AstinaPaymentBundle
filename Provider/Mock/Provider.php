<?php

namespace Astina\Bundle\PaymentBundle\Provider\Mock;

use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;
use Astina\Bundle\PaymentBundle\Provider\ProviderInterface;
use Astina\Bundle\PaymentBundle\Provider\OrderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
class Provider implements ProviderInterface
{	
    public function createTransaction(OrderInterface $order = null)
    {
    	return new Transaction();
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
        return $successUrl;
    }


    public function createTransactionFromRequest(Request $request)
    {
    	return $this->createTransaction();
    }
}