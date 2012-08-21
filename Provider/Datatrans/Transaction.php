<?php

namespace Astina\Bundle\PaymentBundle\Provider\Datatrans;

use Astina\Bundle\PaymentBundle\Provider\AbstractTransaction;

/**
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
class Transaction extends AbstractTransaction
{
    function getRequestTypeAuthorize()
    {
    	return 'NAO';
    }
    
    function getRequestTypeAuthorizeCapture()
    {
    	return 'CAA';
    }
    
    function getRequestTypeCapture()
    {
    	return 'CAO';
    }

    public function isStatusSuccess()
    {
        return $this->getStatus() == "success";
    }

    public function setUseAlias($useAlias)
    {
        if ($useAlias) {
            $useAlias = 'yes';
        }

        parent::setUseAlias($useAlias);
    }
}