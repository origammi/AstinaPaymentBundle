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
        $data = '<IDP MSGTYPE="PayInit" MSG_GUID="5a62ecc72ed145fb935ff5559998d732" CLIENTVERSION="2.2" '.
                'KEYID="0-99867-7d5a273c0f5043e28811e764d6433086" TOKEN="0eb31fbb420c471687914a14e57a1b74" '.
                'ALLOWCOLLECT="no" DELIVERY="yes" EXPIRATION="20121003 10:30:44" ACCOUNTID="99867-94913159" '.
                'AMOUNT="10000" CURRENCY="CHF" DESCRIPTION="123456" SUCCESSLINK="http://success.shop.ch" '.
                'BACKLINK="http://cancel.shop.ch" FAILLINK="http://error.shop.ch" CCNAME="yes" />';

        $signature = 'bdb2d668cd99bf3fd5c6c7c4e14c66350bbc71903de85a29ea5922b411854c63d154c77272ad51d2eb13bee'.
                     '46585808c604227dfdf4a0bc531f10d9143a0a05a';

        return sprintf("https://www.saferpay.com/vt/Pay.asp?DATA=%s&SIGNATURE=%s", urlencode($data), $signature);
    }

    public function verifyPayConfirm($data, $signature)
    {
        return 'OK:ID=56a77rg243asfhmkq3r&TOKEN="bbf6577cd8e74d65a27f084c9cfe2592"';
    }

    public function createPayComplete($transactionId)
    {
        return 'OK';
    }
}
