<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Astina\Bundle\PaymentBundle\Provider\ProviderInterface;
use Astina\Bundle\PaymentBundle\Provider\OrderInterface;
use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;
use Psr\Log\LoggerInterface;

/**
 * Saferpay Provider for V4.1.6
 *
 * @see https://astina.atlassian.net/wiki/download/attachments/3932162/Saferpay+Payment+Page+V4.1.6+EN.pdf
 */
class Provider implements ProviderInterface
{
    const PAYMENT_METHOD = 'Saferpay';

    /** @var SaferpayEndpoint $endpoint*/
    private $endpoint;

    private $translator;

    /** @var $logger LoggerInterface */
    private $logger;

    public function __construct(SaferpayEndpoint $endpoint,
                                TranslatorInterface $translator,
                                LoggerInterface $logger)
    {
        $this->endpoint = $endpoint;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @param \Astina\Bundle\PaymentBundle\Provider\OrderInterface $order
     * @return \Astina\Bundle\PaymentBundle\Provider\TransactionInterface
     */
    public function createTransaction(OrderInterface $order = null)
    {
        $transaction = new Transaction();

        if ($order) {
            $transaction->setAmount($order->getTotalPrice());
            $transaction->setCurrency($order->getCurrency());
            $transaction->setReference($order->getOrderNumber());
        }

        // description is mandatory for Saferpay
        $transaction->setDescription($this->translator->trans('payment.saferpay.description'));

        return $transaction;
    }

    /**
     * @param \Astina\Bundle\PaymentBundle\Provider\TransactionInterface $transaction
     * @throws \Exception
     */
    public function authorizeTransaction(TransactionInterface $transaction)
    {
        if($transaction->getResponseMessage() == null ||
           $transaction->getTransactionToken() == null) {
            throw new \Exception('Unable to authorize transaction without DATA or SIGNATURE');
        }

        $verificationMessage = $this->endpoint->verifyPayConfirm($transaction->getResponseMessage(), $transaction->getTransactionToken());

        if(substr($verificationMessage, 0, 2) != 'OK') {
            throw new \Exception('Unable to verify transaction: ' . $verificationMessage);
        }

        preg_match('/ID=([^&]+)/', $verificationMessage, $matches);

        $transaction->setTransactionId($matches[1]);

        if ($providerName = $this->findProviderName($transaction)) {
        	$transaction->setProviderName($providerName);
        }

        $this->logger->info('Authorized Saferpay transaction', array('transactionId' => $transaction->getTransactionId()));
    }

    /**
     * @param TransactionInterface $transaction
     * @throws \Exception
     */
    public function captureTransaction(TransactionInterface $transaction)
    {
        $captureMessage = $this->endpoint->createPayComplete($transaction->getTransactionId());
        if(substr($captureMessage, 0, 2) != 'OK') {
            throw new \Exception('Unable to verify transaction: ' . $captureMessage);
        }
        $transaction->setStatus($captureMessage);

        $transaction->setPaymentMethod(self::PAYMENT_METHOD);

        if ($providerName = $this->findProviderName($transaction)) {
        	$transaction->setProviderName($providerName);
        }

        $this->logger->info('Captured Saferpay transaction', array('transactionId' => $transaction->getTransactionId()));
    }

    private function findProviderName(TransactionInterface $transaction)
    {
    	return $this->getResponseValue('PROVIDERNAME', $transaction->getResponseMessage());
    }

    /**
     * @param TransactionInterface $transaction
     * @param string $successUrl
     * @param string $errorUrl
     * @param string $cancelUrl
     * @param array $params
     * @return string
     */
    public function createPaymentUrl(TransactionInterface $transaction,
                              $successUrl = null,
                              $errorUrl = null,
                              $cancelUrl = null,
                              array $params = array())
    {
        return $this->endpoint->retrievePaymentLink($transaction, $successUrl, $errorUrl, $cancelUrl, $params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param Request $request
     * @return \Astina\Bundle\PaymentBundle\Provider\Saferpay\Transaction|\Astina\Bundle\PaymentBundle\Provider\TransactionInterface
     */
    public function createTransactionFromRequest(Request $request)
    {
        // use DATA and SIGNATURE parameters for VerifyPayConfirm
        $transaction = new Transaction();

        $data = $request->get('DATA');

        $transaction->setResponseMessage($data);
        $transaction->setTransactionToken($request->get('SIGNATURE'));
        $transaction->setProviderName($this->getResponseValue('PROVIDERNAME', $data));
        $transaction->setAmount($this->getResponseValue('AMOUNT', $data));
        $transaction->setCurrency($this->getResponseValue('CURRENCY', $data));
        $transaction->setTransactionId($this->getResponseValue('ID', $data));
        $transaction->setReference($this->getResponseValue('ORDERID', $data));

        return $transaction;
    }

    private function getResponseValue($name, $data)
    {
        preg_match(sprintf('/%s="([^"]+)/', $name), $data, $matches);

        return count($matches) > 0 ? $matches[1] : null;
    }
}
