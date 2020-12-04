<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\PaymentException;
use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class HttpsSaferpayEndpoint implements SaferpayEndpoint
{
    const SAFERPAY_BASE_URL = 'https://www.saferpay.com/api/';
    const SAFERPAY_TEST_URL = 'https://test.saferpay.com/api/';
    const SPEC_VERSION = '1.20';

    /** @var string $accountId */
    private $accountId;

    /** @var string $password */
    private $password;

    /** @var string $testmode */
    private $testmode;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $terminalId;

    /**
     * @var string
     */
    private $username;

    public function __construct(LoggerInterface $logger,
                                $accountId,
                                $password = null,
                                $testmode = true,
                                $vtConfig = null,
                                $username = null,
                                $terminalId = null)
    {
        if (!$terminalId) {
            throw new PaymentException('Terminal ID is required');
        }

        if (!$username || !$password) {
            throw new PaymentException('Username and password is required');
        }

        $this->logger = $logger;
        $this->accountId = $accountId;
        $this->username = $username;
        $this->password = $password;
        $this->testmode = $testmode;
        $this->terminalId = $terminalId;
    }

    /**
     * @param $transaction
     * @param $successUrl
     * @param $errorUrl
     * @param $cancelUrl
     * @param $params
     * @return string
     */
    public function retrievePaymentLink(TransactionInterface $transaction, $successUrl, $errorUrl, $cancelUrl, $params)
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
     *
     * @return array
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
                'OrderId' => $params['ORDERID'] ?? '',
                'Description' => $transaction->getDescription(),
            ],
            'Payer' => [
                'LanguageCode' => $params['LANGID'] ?? 'de',
            ],
            'ReturnUrls' => [
                'Success' => $successUrl,
                'Fail' => $errorUrl,
                'Abort' => $cancelUrl,
            ],
            'Notification' => [
                'NotifyUrl' => $params['NOTIFYURL'] ?? '',
            ],
        ];

        $response = $this->executeRequest('Payment/v1/PaymentPage/Initialize', $queryParams);

        $redirectUrl = $response['RedirectUrl'] ?? null;
        $token = $response['Token'] ?? null;
        if (empty($redirectUrl) || empty($token)) {
            throw new PaymentException('Failed to initialize payment');
        }

        return [
            'redirectUrl' => $redirectUrl,
            'token' => $token,
        ];
    }

    /**
     * @param $paymentToken
     * @return array
     */
    public function assertPayment($paymentToken)
    {
        $assert = $this->executeRequest('Payment/v1/PaymentPage/Assert', [
            'Token' => $paymentToken
        ]);

        return $assert;
    }

    public function createPayComplete($transactionId)
    {
        $payComplete =  $this->executeRequest('Payment/v1/Transaction/Capture', [
            'TransactionReference' => [
                'TransactionId' => $transactionId,
            ]
        ]);

        return $payComplete;
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
                'SpecVersion' => self::SPEC_VERSION,
                'CustomerId' => $this->accountId,
                "RequestId" => Uuid::uuid4()->toString(),
                "RetryIndicator" => 0
            ],
            'TerminalId' => $this->terminalId,
        ];

        $json = $header + $params;
        $this->logger->debug('Sending Saferpay API request: ' . $endpoint, $json);

        $url = ($this->testmode ? self::SAFERPAY_TEST_URL : self::SAFERPAY_BASE_URL). $endpoint;

        $client = new Client();
        $response = $client->post(
            $url,
            [
                'json' => $json,
                'auth' => [$this->username, $this->password],
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}
