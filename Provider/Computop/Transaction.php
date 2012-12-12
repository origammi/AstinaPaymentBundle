<?php

namespace Astina\Bundle\PaymentBundle\Provider\Computop;

use Astina\Bundle\PaymentBundle\Provider\AbstractTransaction;

class Transaction extends AbstractTransaction
{
    function isStatusSuccess()
    {
        return $this->getStatus() == 'AUTHORIZED';
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
