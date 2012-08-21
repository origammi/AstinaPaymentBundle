<?php

namespace Astina\Bundle\PaymentBundle\Provider\Datatrans;

use Astina\Bundle\PaymentBundle\Provider\AuthorizationException;

/**
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
class XmlResponse
{
	private $doc;
	
	public function __construct($data)
	{
		try {
			$this->doc = new \SimpleXMLElement($data);
		} catch (\Exception $e){
			throw new AuthorizationException('Failed to parse XML response', $e->getCode(), $e);
		}
	}
	
	public function merge(Transaction $transaction)
	{
        $body = $this->doc->body;
        $transaction->setStatus($body->status);
        
        $trans = $body->transaction;
        
		// compare reference
		if ($trans->attributes()->refno != $transaction->getReference()) {
		    throw new \InvalidArgumentException('Transaction reference and response refno do not match');
		}
		
		$response = $trans->response;
		$transaction->setResponseCode((string) $response->responseCode);
		$transaction->setResponseMessage((string) $response->responseMessage);
		$transaction->setTransactionId((string) $response->uppTransactionId);
		$transaction->setAuthorizationCode((string) $response->authorizationCode);
		$transaction->setIssuerAuthorizationCode((string) $response->acqAuthorizationCode);
		$transaction->setMaskedCardNumber((string) $response->maskedCC);
	}
}