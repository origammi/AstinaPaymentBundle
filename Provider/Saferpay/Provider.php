<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\PaymentException;
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

    private $translator;

    /** @var $logger LoggerInterface */
    private $logger;

    /** @var SaferpayEndpoint $endpoint*/
    private $endpoint;

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
        // authorization is done in createPaymentUrl()
    }

    /**
     * @param TransactionInterface $transaction
     * @throws \Exception
     */
    public function captureTransaction(TransactionInterface $transaction)
    {
        $response = $this->endpoint->createPayComplete($transaction->getTransactionId());

        $transaction->setStatus($response['Status']);
        $transaction->setPaymentMethod(self::PAYMENT_METHOD);

        $this->logger->info('Captured Saferpay transaction', array('transactionId' => $transaction->getTransactionId()));
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
     * @param TransactionInterface $transaction
     * @param string $successUrl
     * @param string $errorUrl
     * @param string $cancelUrl
     * @param array $params
     * @return array
     */
    public function initializePayment(TransactionInterface $transaction,
        $successUrl = null,
        $errorUrl = null,
        $cancelUrl = null,
        array $params = array())
    {
        return $this->endpoint->initializePayment($transaction, $successUrl, $errorUrl, $cancelUrl, $params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param Request $request
     * @return \Astina\Bundle\PaymentBundle\Provider\Saferpay\Transaction|\Astina\Bundle\PaymentBundle\Provider\TransactionInterface
     */
    public function createTransactionFromRequest(Request $request)
    {
        $paymentToken = $request->attributes->get('paymentToken');
        if (!$paymentToken) {
            throw new PaymentException('"paymentToken" is expected at request attributes');
        }

        $data = $this->endpoint->assertPayment($paymentToken);

        $transactionData = $data['Transaction'] ?? [];
        $transaction = new Transaction();

        $transaction->setResponseMessage($data);
        $transaction->setTransactionToken($transactionData['SixTransactionReference'] ?? '');
        $transaction->setProviderName($transactionData['AcquirerName'] ?? '');
        $transaction->setAmount($transactionData['Amount']['Value']);
        $transaction->setCurrency($transactionData['Amount']['CurrencyCode']);
        $transaction->setTransactionId($transactionData['Id'] ?? '');
        $transaction->setReference($transactionData['OrderId']);

        return $transaction;
    }
}
