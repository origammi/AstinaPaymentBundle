<?php

namespace Astina\Bundle\PaymentBundle\Provider;

/**
 * @author $Author: pkraeutli $
 * @version $Revision:  $, $Date: 5/26/12 $
 */
interface OrderInterface
{
    public function getTotalPrice();

    public function getBasePrice();

    public function getDeliveryPrice();

    public function getCurrency();
}
