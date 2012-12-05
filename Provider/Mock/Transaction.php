<?php

namespace Astina\Bundle\PaymentBundle\Provider\Mock;

use Astina\Bundle\PaymentBundle\Provider\AbstractTransaction;

/**
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
class Transaction extends AbstractTransaction
{
    public function __construct()
    {
        $this->setPaymentMethod('mock');
        $this->setProviderName('mock');
        $this->setTransactionId('mock');
    }

    public function isStatusSuccess()
    {
        return true;
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