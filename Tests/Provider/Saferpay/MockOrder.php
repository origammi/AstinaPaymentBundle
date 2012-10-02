<?php

namespace Astina\Bundle\PaymentBundle\Tests\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\OrderInterface;

class MockOrder implements OrderInterface
{
    public function getTotalPrice()
    {
        return 10000;
    }

    public function getBasePrice()
    {
        return 8000;
    }

    public function getDeliveryPrice()
    {
        return 2000;
    }

    public function getCurrency()
    {
        return 'CHF';
    }
}
