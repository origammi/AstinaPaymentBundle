<?php

namespace Astina\Bundle\PaymentBundle\Provider\Paypal;

use Astina\Bundle\PaymentBundle\Provider\AbstractTransaction;

/**
 * @author $Author: pkraeutli $
 * @version $Revision:  $, $Date: 5/26/12 $
 */
class Transaction extends AbstractTransaction
{
    public function isStatusSuccess()
    {
        return strtolower($this->getStatus()) == 'success';
    }

    function getRequestTypeAuthorize()
    {
        return 'Authorization';
    }

    function getRequestTypeAuthorizeCapture()
    {
    }

    function getRequestTypeCapture()
    {
        return 'Sale';
    }

}
