<?php

namespace Astina\Bundle\PaymentBundle\Tests\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\Saferpay\SaferpayEndpoint;
use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;

class MockSaferpayEndpoint implements SaferpayEndpoint
{
    /**
     * @param $paymentInitUrl
     * @return string
     */
    public function retrievePaymentLink(TransactionInterface $transaction, $successUrl, $errorUrl, $cancelUrl, $params)
    {
        return 'https://test.saferpay.com/vt2/api/PaymentPage/254472/17727632/bja4oclof1ki7mdr2eyby5iko';
    }

    public function assertPayment($paymentToken)
    {
        return json_decode('
            {
              "ResponseHeader": {
                "SpecVersion": "1.20",
                "RequestId": "945353ff-62a6-4e6f-9106-2211f9bfe8e1"
              },
              "Transaction": {
                "Type": "PAYMENT",
                "Status": "AUTHORIZED",
                "Id": "Mx1M6UAMW49zUAYU2I4YbSArz8OA",
                "Date": "2020-11-23T09:15:17.790+01:00",
                "Amount": {
                  "Value": "3340",
                  "CurrencyCode": "CHF"
                },
                "OrderId": "201123078",
                "AcquirerName": "VISA Saferpay Test",
                "AcquirerReference": "05125315423",
                "SixTransactionReference": "0:0:3:Mx1M6UAMW49zUAYU2I4YbSArz8OA",
                "ApprovalCode": "789326"
              },
              "PaymentMeans": {
                "Brand": {
                  "PaymentMethod": "VISA",
                  "Name": "VISA"
                },
                "DisplayText": "xxxx xxxx xxxx 0007",
                "Card": {
                  "MaskedNumber": "xxxxxxxxxxxx0007",
                  "ExpYear": 2020,
                  "ExpMonth": 11,
                  "HolderName": "Yamada Taro",
                  "CountryCode": "JP"
                }
              },
              "Payer": {
                "IpAddress": "37.214.24.234",
                "IpLocation": "BY"
              },
              "Liability": {
                "LiabilityShift": true,
                "LiableEntity": "ThreeDs",
                "ThreeDs": {
                  "Authenticated": true,
                  "LiabilityShift": true,
                  "Xid": "de0581a3-37b1-404f-a11e-6e70efeaef31"
                }
              },
              "Dcc": {
                "PayerAmount": {
                  "Value": "4601",
                  "CurrencyCode": "JPY"
                }
              }
            }
        ', true);
    }

    public function createPayComplete($transactionId)
    {
        return json_decode('
            {
              "ResponseHeader": {
                "SpecVersion": "1.20",
                "RequestId": "f7cae5d9-b024-4c1a-a83b-bff455cd0457"
              },
              "CaptureId": "jpnj8dbd00vhUA1pKxxnA4EjIOIA_c",
              "Status": "CAPTURED",
              "Date": "2020-11-23T09:15:30.006+01:00"
            }
        ', true);
    }

    public function initializePayment(
        TransactionInterface $transaction,
        $successUrl = null,
        $errorUrl = null,
        $cancelUrl = null,
        array $params = []
    ) {
        return [
            'redirectUrl' => 'https://test.saferpay.com/vt2/api/PaymentPage/254472/17727632/bja4oclof1ki7mdr2eyby5iko',
            'token' => 'bja4oclof1ki7mdr2eyby5iko',
        ];
    }
}
