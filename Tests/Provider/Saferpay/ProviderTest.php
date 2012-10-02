<?php

namespace Astina\Bundle\PaymentBundle\Tests\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\Saferpay\Provider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\Container;
use Astina\Bundle\PaymentBundle\Provider\Saferpay\HttpsSaferpayEndpoint;

class ProviderTest extends WebTestCase
{
    /** @var $container Container */
    private $container;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        /** @var $kernel Kernel */
        $kernel = static::createKernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();
    }

    public function testPayment()
    {
        $provider = new Provider(new MockSaferpayEndpoint());

        $transaction = $provider->createTransaction(new MockOrder());

        $transaction->setReference('123456');

        $paymentUrl = $provider->createPaymentUrl($transaction, 'http://success.shop.ch', 'http://error.shop.ch', 'http://cancel.shop.ch');

        $this->assertNotNull($paymentUrl);

        $request = new Request();

        $data = '<IDP MSGTYPE="PayConfirm" TOKEN="(unused)" VTVERIFY="(obsolete)" KEYID="1-0" '.
                'ID="fIWvUhbAb0bpvA6CSMO6Az9Cb0YA" ACCOUNTID="99867-94913159" PROVIDERID="90" '.
                'PROVIDERNAME="Saferpay Test Card" AMOUNT="10000" CURRENCY="CHF" IP="80.218.27.103" '.
                'IPCOUNTRY="CH" CCCOUNTRY="US" MPI_LIABILITYSHIFT="yes" MPI_TX_CAVV="AAABBIIFmAAAAAAAAAAAAAAAAAA=" '.
                'MPI_XID="V3dbbAlZSy0kBA4DRT4IZgVWIQY=" ECI="1" CAVV="AAABBIIFmAAAAAAAAAAAAAAAAAA=" '.
                'XID="V3dbbAlZSy0kBA4DRT4IZgVWIQY=" />';

        $signature = '32aa00e17ae970f917a0a02d5917195b289bf85b789ffe2c4cafd3a87affc03cbaccabd424b2a533d22e108ca'.
                     '90bd494935755b0b105f1786e8292e886e66bb0';

        $request->query->add(array('DATA' => $data, 'SIGNATURE' => $signature));

        $transaction = $provider->createTransactionFromRequest($request);

        $provider->authorizeTransaction($transaction);

        $this->assertEquals($transaction->getTransactionId(), '56a77rg243asfhmkq3r');

        $provider->captureTransaction($transaction);

        $this->assertTrue($transaction->isStatusSuccess());
    }
}