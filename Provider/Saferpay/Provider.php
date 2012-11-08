<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;

use Astina\Bundle\PaymentBundle\Provider\ProviderInterface;
use Astina\Bundle\PaymentBundle\Provider\OrderInterface;
use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;
use Astina\Bundle\PaymentBundle\Provider\Saferpay\SaferpayEndpoint;
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

    public function __construct(SaferpayEndpoint $endpoint,
                                Translator $translator)
    {
        $this->endpoint = $endpoint;
        $this->translator = $translator;
    }

    /**
     * @return \Astina\Bundle\PaymentBundle\Provider\TransactionInterface
     */
    function createTransaction(OrderInterface $order = null)
    {
        $transaction = new Transaction();

        $transaction->setAmount($order->getTotalPrice());
        $transaction->setCurrency($order->getCurrency());

        // description is mandatory for Saferpay
        $transaction->setReference($this->translator->trans('payment.saferpay.description'));

        return $transaction;
    }

    /**
     * @param \Astina\Bundle\PaymentBundle\Provider\TransactionInterface $transaction
     */
    function authorizeTransaction(TransactionInterface $transaction)
    {
        if($transaction->getResponseMessage() == null ||
           $transaction->getTransactionToken() == null) {
            throw new \Exception('Unable to authorize transaction without DATA or SIGNATURE: ' . $verificationMessage);
        }

        $verificationMessage = $this->endpoint->verifyPayConfirm($transaction->getResponseMessage(), $transaction->getTransactionToken());

        if(substr($verificationMessage, 0, 2) != 'OK') {
            throw new \Exception('Unable to verify transaction: ' . $verificationMessage);
        }

        preg_match('/ID=([^&]+)/', $verificationMessage, $matches);

        $transaction->setTransactionId($matches[1]);
    }

    /**
     * @param TransactionInterface $transaction
     */
    function captureTransaction(TransactionInterface $transaction)
    {
        $captureMessage = $this->endpoint->createPayComplete($transaction->getTransactionId());
        if(substr($captureMessage, 0, 2) != 'OK') {
            throw new \Exception('Unable to verify transaction: ' . $captureMessage);
        }
        $transaction->setStatus($captureMessage);
        $transaction->setPaymentMethod(self::PAYMENT_METHOD);
    }

    /**
     * @param TransactionInterface $transaction
     * @param string $successUrl
     * @param string $errorUrl
     * @param string $cancelUrl
     * @param array $params
     * @return string
     */
    function createPaymentUrl(TransactionInterface $transaction,
                              $successUrl = null,
                              $errorUrl = null,
                              $cancelUrl = null,
                              array $params = array())
    {
        return $this->endpoint->retrievePaymentLink($transaction, $successUrl, $errorUrl, $cancelUrl, $params);
    }

    /**
     * @param Request $request
     * @return |Astina\Bundle\PaymentBundle\Provider\TransactionInterface
     */
    public function createTransactionFromRequest(Request $request)
    {
        // use DATA and SIGNATURE parameters for VerifyPayConfirm
        $transaction = new Transaction();

        $transaction->setResponseMessage($request->get('DATA'));
        $transaction->setTransactionToken($request->get('SIGNATURE'));

        return $transaction;
    }
}
