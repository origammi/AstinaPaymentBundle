<?php

namespace Astina\Bundle\PaymentBundle\Provider\Datatrans;

use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;

/**
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
class CaptureXmlRequest extends XmlRequest
{
    public function __construct($merchantId, TransactionInterface $transaction)
    {
    	$this->merchantId = $merchantId;
    	$this->transaction = $transaction;
    	
        $root = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<paymentService />');
        
        $this->addRequestContent($root);
        
        $request = $root->body->transaction->request;
        $request->addChild('uppTransactionId', $transaction->getTransactionId());
        
        $this->doc = $root;
    }
}