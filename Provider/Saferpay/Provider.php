<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\PaymentException;
use GuzzleHttp\Client;
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

    public function __construct(SaferpayEndpoint $endpoint,
        TranslatorInterface $translator,
        LoggerInterface $logger)
    {
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
        $response = $this->executeRequest('Payment/v1/Transaction/Capture', [
            'TransactionReference' => [
                'TransactionId' => $transaction->getTransactionId(),
            ]
        ]);

        $transaction->setStatus($response['Status']);

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
     * @param string $endpoint
     * @param array $params
     *
     * @return array
     */
    private function executeRequest($endpoint, $params): array
    {
        $header = [
            'RequestHeader' => [
                'SpecVersion' => $specVersion,
                'CustomerId' => $customerId,
                "RequestId" => "QQQQ",
                "RetryIndicator" => 0
            ],
            'TerminalId' => $terminalId,
        ];


        $client = new Client();
        $response = $client->post(
            'https://test.saferpay.com/api/' . $endpoint,
            [
                'json' => $header + $params,
                'auth' => [$username, $password],
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
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
        $response = $this->initializePayment($successUrl, $errorUrl, $cancelUrl, $params);

        return $response['url'];
    }

    /**
     * @param TransactionInterface $transaction
     * @param string $successUrl
     * @param string $errorUrl
     * @param string $cancelUrl
     * @param array $params
     * @return string
     */
    public function initializePayment(TransactionInterface $transaction,
        $successUrl = null,
        $errorUrl = null,
        $cancelUrl = null,
        array $params = array())
    {
        $queryParams = [
            'Payment' => [
                'Amount' => [
                    'Value' => $transaction->getAmount(),
                    'CurrencyCode' => $transaction->getCurrency(),
                ],
                'OrderId' => isset($params['ORDERID']) ? $params['ORDERID'] : '',
                'Description' => 'Order #'.isset($params['ORDERID']) ? $params['ORDERID'] : '',
            ],
            'Payer' => [
                'LanguageCode' => isset($params['LANGID']) ? $params['LANGID'] : 'de',
            ],
            'ReturnUrls' => [
                'Success' => $successUrl,
                'Fail' => $errorUrl,
                'Abort' => $cancelUrl,
            ],
            'Notification' => [
                'NotifyUrl' => isset($params['NOTIFYURL']) ? $params['NOTIFYURL'] : '',
            ],
        ];

        $response = $this->executeRequest('Payment/v1/PaymentPage/Initialize', $queryParams);

        $redirectUrl = isset($response['RedirectUrl']) ? $response['RedirectUrl'] : null;
        $token = isset($response['Token']) ? $response['Token'] : null;
        if (empty($redirectUrl) || empty($token)) {
            throw new PaymentException('Failed to initialize payment');
        }

        return [
            'redirectUrl' => $redirectUrl,
            'token' => $token,
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param Request $request
     * @return \Astina\Bundle\PaymentBundle\Provider\Saferpay\Transaction|\Astina\Bundle\PaymentBundle\Provider\TransactionInterface
     */
    public function createTransactionFromRequest(Request $request)
    {
        $paymentToken = $request->attributes->get('paymentToken');
        $data = $this->executeRequest('Payment/v1/PaymentPage/Assert', ['Token' => $paymentToken]);

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

    private function getResponseValue($name, $data)
    {
        preg_match(sprintf('/%s="([^"]+)/', $name), $data, $matches);

        return count($matches) > 0 ? $matches[1] : null;
    }
}
