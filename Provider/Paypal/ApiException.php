<?php

namespace Astina\Bundle\PaymentBundle\Provider\Paypal;

/**
 * @author $Author: pkraeutli $
 * @version $Revision:  $, $Date: 5/26/12 $
 */
class ApiException extends \RuntimeException
{
    private $response;

    public function __construct($message, array $response = null)
    {
        $code = null;

        if ($response) {
            $this->response = $response;

            $message = sprintf('%s: %s', $message, isset($response['L_LONGMESSAGE0']) ? $response['L_LONGMESSAGE0'] : null);
            $code = isset($response['L_ERRORCODE0']) ? $response['L_ERRORCODE0'] : null;
        }

        parent::__construct($message, $code);
    }
}
