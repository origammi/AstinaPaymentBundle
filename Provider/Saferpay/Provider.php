<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
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

    /** @var $logger \Symfony\Component\HttpKernel\Log\LoggerInterface */
    private $logger;

    public function __construct(SaferpayEndpoint $endpoint,
                                Translator $translator,
                                LoggerInterface $logger)
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

        if ($order) {
            $transaction->setAmount($order->getTotalPrice());
            $transaction->setCurrency($order->getCurrency());
        }

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
            throw new \Exception('Unable to authorize transaction without DATA or SIGNATURE');
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

        $transaction->setProviderName($this->getResponseValue('PROVIDERNAME', $transaction->getResponseMessage()));
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

        $data = $request->get('DATA');

        $transaction->setResponseMessage($data);
        $transaction->setTransactionToken($request->get('SIGNATURE'));
        $transaction->setProviderName($this->getResponseValue('PROVIDERNAME', $data));
        $transaction->setAmount($this->getResponseValue('AMOUNT', $data));
        $transaction->setCurrency($this->getResponseValue('CURRENCY', $data));
        $transaction->setTransactionId($this->getResponseValue('ID', $data));

        return $transaction;
    }

    private function getResponseValue($name, $data)
    {
        preg_match(sprintf('/%s="([^"]+)/', $name), $data, $matches);
        return count($matches) > 0 ? $matches[1] : null;
    }
}
