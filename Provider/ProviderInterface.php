<?php

namespace Astina\Bundle\PaymentBundle\Provider;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
interface ProviderInterface
{
	/**
	 * @return \Astina\Bundle\PaymentBundle\Provider\TransactionInterface
	 */
	function createTransaction(OrderInterface $order = null);

    /**
     * @param \Astina\Bundle\PaymentBundle\Provider\TransactionInterface $transaction
     */
	function authorizeTransaction(TransactionInterface $transaction);
    
    /**
     * @param TransactionInterface $transaction
     */
    function captureTransaction(TransactionInterface $transaction);

    /**
     * @param TransactionInterface $transaction
     * @param string $successUrl
     * @param string $errorUrl
     * @param string $cancelUrl
     * @param array $params
     * @return string
     */
	function createPaymentUrl(TransactionInterface $transaction, $successUrl = null, $errorUrl = null, $cancelUrl = null, array $params = array());
	
	/**
	 * @param Request $request
	 * @return \Astina\Bundle\PaymentBundle\Provider\TransactionInterface
	 */
	public function createTransactionFromRequest(Request $request);
}