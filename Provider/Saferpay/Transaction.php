<?php

namespace Astina\Bundle\PaymentBundle\Provider\Saferpay;

use Astina\Bundle\PaymentBundle\Provider\AbstractTransaction;

class Transaction extends AbstractTransaction
{
    function isStatusSuccess()
    {
        return strtolower($this->getStatus()) == 'ok';
    }

    function getRequestTypeAuthorize()
    {

    }

    function getRequestTypeAuthorizeCapture()
    {

    }

    function getRequestTypeCapture()
    {

    }
}
