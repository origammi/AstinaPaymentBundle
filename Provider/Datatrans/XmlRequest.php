<?php

namespace Astina\Bundle\PaymentBundle\Provider\Datatrans;

use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;

/**
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
abstract class XmlRequest
{
	protected $doc;
	
	protected $merchantId;
	
	/**
     * @var Astina\Bundle\PaymentBundle\Provider\TransactionInterface
	 */
	protected $transaction;
	
	protected function addRequestContent(\SimpleXMLElement $elem)
	{
        $elem->addAttribute('version', '1');
        
        $body = $elem->addChild('body');
        $body->addAttribute('merchantId', $this->merchantId);
        
        $trans = $body->addChild('transaction');
        $trans->addAttribute('refno', $this->transaction->getReference());
        
        $request = $trans->addChild('request');
        $request->addChild('amount', $this->transaction->getAmount());
        $request->addChild('currency', $this->transaction->getCurrency());
        if ($this->transaction->getCardAlias()) {
            $request->addChild('aliasCC', $this->transaction->getCardAlias());    
        } else {
            $request->addChild('CC', $this->transaction->getCardNumber());
        }
        $request->addChild('expm', $this->transaction->getExpMonth());
        $request->addChild('expy', $this->transaction->getExpYear());
	}
	
	public function createContext()
	{
        $xmlData = $this->doc->asXml();

//        $contentType = 'application/x-www-form-urlencoded';
//        $content = sprintf('xmlRequest=%s', urlencode($xmlData));

        $contentType = 'application/xml';
        $content = $xmlData;
        
        $context = stream_context_create(
            array('http' => array(
                'method' => 'POST',
                'header' => "Content-type: " . $contentType . "\n" .
                    "Content-length: " . strlen($content) . "\n",
                'content' => $content
            ))
        );
        
        return $context;
	}
}