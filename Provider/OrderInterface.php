<?php

namespace Astina\Bundle\PaymentBundle\Provider;

interface OrderInterface
{
    public function getOrderNumber();

    public function getTotalPrice();

    public function getBasePrice();

    public function getDeliveryPrice();

    public function getCurrency();
}
