<?php

namespace Astina\Bundle\PaymentBundle\Provider\Datatrans;

use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;

/**
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
class AuthorizationXmlRequest extends XmlRequest
{
    
    public function __construct($merchantId, TransactionInterface $transaction)
    {
        $this->merchantId = $merchantId;
        $this->transaction = $transaction;
        
        $root = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<authorizationService />');
        
        $this->addRequestContent($root);
        
        $request = $root->body->transaction->request;
        $request->addChild('reqtype', $this->transaction->getRequestType());
        
        $this->doc = $root;
    }
}