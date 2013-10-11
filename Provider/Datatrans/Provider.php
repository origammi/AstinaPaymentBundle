<?php

namespace Astina\Bundle\PaymentBundle\Provider\Datatrans;

use Astina\Bundle\PaymentBundle\Provider\SecurityException;
use Astina\Bundle\PaymentBundle\Provider\AuthorizationException;
use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;
use Astina\Bundle\PaymentBundle\Provider\ProviderInterface;
use Astina\Bundle\PaymentBundle\Provider\OrderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Datatrans provider
 * See https://pilot.datatrans.biz/showcase/doc/Technical_Implementation_Guide.pdf for specs
 */
class Provider implements ProviderInterface
{
	/**
	 * @var string
	 */
	private $merchantId;
	
	/**
	 * @var string
	 */
	private $serviceUrl;
	
	/**
	 * @var string
	 */
	private $authorizeXmlUrl;
	
	/**
	 * @var string
	 */
	private $captureXmlUrl;
	
	/**
	 * Key to sign requests
	 * @var string
	 */
	private $sign;
	
	/**
	 * Key to validate responses
	 * @var string
	 */
	private $sign2;
	
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	
	const REQTYPE_NOA = 'NOA';
	const REQTYPE_CAA = 'CAA';
	const REQTYPE_CAO = 'CAO';
	
	const RESPONSE_CODE_OK = '01';
	
	public function __construct($merchantId, $serviceUrl, $authorizeXmlUrl, $captureXmlUrl,
	    $sign, $sign2, LoggerInterface $logger)
	{
		$this->merchantId = $merchantId;
		$this->serviceUrl = $serviceUrl;
		$this->authorizeXmlUrl = $authorizeXmlUrl;
		$this->captureXmlUrl = $captureXmlUrl;
		$this->sign = $sign;
		$this->sign2 = $sign2;
		$this->logger = $logger;
	}
	
    public function createTransaction(OrderInterface $order = null)
    {
    	$transaction = new Transaction();
    	
    	/*
    	 * needs to be properly set in the controller where we have
    	 * the request/user locale. set here only to assure it is not null
    	 */
    	$transaction->setLanguage('de');
    	
    	return $transaction;
    }
    
    public function authorizeTransaction(TransactionInterface $transaction)
    {
    	$this->logger->info('Start authorize transaction', array('ref' => $transaction->getReference()));
    	
    	$transaction->setRequestType(self::REQTYPE_NOA);
    	
        $xmlRequest = new AuthorizationXmlRequest($this->merchantId, $transaction);
        $context = $xmlRequest->createContext();
        $responseContent = file_get_contents($this->authorizeXmlUrl, null, $context);
        $this->logger->debug('XML response content', array('data' => $responseContent));        
        $xmlResponse = new XmlResponse($responseContent);
        $xmlResponse->merge($transaction);
    	
    	if ($transaction->getResponseCode() != self::RESPONSE_CODE_OK) {
    		throw new AuthorizationException('Failed to authorize transaction');
    	}
    	
    	return $transaction;
    }
    
    public function captureTransaction(TransactionInterface $transaction)
    {
        $this->logger->info('Start capture transaction', array('ref' => $transaction->getReference()));
        
        $xmlRequest = new CaptureXmlRequest($this->merchantId, $transaction);
        $context = $xmlRequest->createContext();
        $responseContent = file_get_contents($this->captureXmlUrl, null, $context);
        $this->logger->debug('XML response content', array('data' => $responseContent));       
        $xmlResponse = new XmlResponse($responseContent);
        $xmlResponse->merge($transaction);
        
        if ($transaction->getResponseCode() != self::RESPONSE_CODE_OK) {
            throw new AuthorizationException('Failed to capture transaction');
        }
        
        return $transaction;
    }
    
    public function createPaymentUrl(TransactionInterface $transaction, $successUrl = null, $errorUrl = null, $cancelUrl = null, array $params = array())
    {
    	$params = array_merge($params, array(
    	    'merchantId' => $this->merchantId,
    	    'amount' => $transaction->getAmount(),
    	    'currency' => $transaction->getCurrency(),
    	    'refno' => $transaction->getReference(),
    	    'language' => $transaction->getLanguage(),
    	    'reqtype' => $transaction->getRequestType(),
            'successUrl' => $successUrl,
            'errorUrl' => $errorUrl,
            'cancelUrl' => $cancelUrl,
    	));
    	
    	if ($transaction->getUseAlias()) {
    		$params['useAlias'] = $transaction->getUseAlias();
    	}
    	
    	// sign?
    	if ($this->sign) {
    		$params['sign'] = $this->createSignature($params);
    	}
    	
    	$paramStr = array();
    	foreach ($params as $name => $value) {
    		$paramStr[] = sprintf('%s=%s', $name, urlencode($value));
    	}
    	
    	$url = sprintf('%s?%s', $this->serviceUrl, implode('&', $paramStr));
    	
        return $url;
    }
    
    public function createTransactionFromRequest(Request $request)
    {
    	$transaction = new Transaction();
    	
    	if ($this->sign) {
    		$this->validateSignature($request);
    	}
    	
    	$transaction->setTransactionId($request->get('uppTransactionId'));
    	$transaction->setAuthorizationCode($request->get('authorizationCode'));
    	$transaction->setResponseCode($request->get('responseCode'));
    	$transaction->setResponseMessage($request->get('responseMessage'));
    	$transaction->setReference($request->get('refno'));
    	$transaction->setAmount($request->get('amount'));
    	$transaction->setCurrency($request->get('currency'));
    	$transaction->setPaymentMethod($request->get('pmethod'));
    	$transaction->setRequestType($request->get('reqtype'));
    	$transaction->setIssuerAuthorizationCode($request->get('acqAuthorizationCode'));
    	$transaction->setStatus($request->get('status'));
    	$transaction->setCardAlias($request->get('aliasCC'));
    	$transaction->setMaskedCardNumber($request->get('maskedCC'));
    	$transaction->setExpMonth($request->get('expm'));
    	$transaction->setExpYear($request->get('expy'));
    	
    	return $transaction;
    }
    
    private function createSignature($params)
    {
    	$data = $params['merchantId'] 
    		  . $params['amount'] 
    		  . $params['currency'] 
    		  . $params['refno'];
    	$key = pack('H*', $this->sign); // convert from hex to binary
    	return hash_hmac('md5', $data, $key);
    }
    
    private function validateSignature(Request $request)
    {
    	if ($this->sign2) {
    		$signature = $request->get('sign2');
    	} else {
    		$signature = $request->get('sign');
    	}
    	
    	if (empty($signature)) {
    		throw new SecurityException('Signature missing in response');
    	}
    	
    	$data = $request->get('merchantId')
    	      . $request->get('amount') 
    	      . $request->get('currency') 
    	      . $request->get('uppTransactionId');
    	$key = pack('H*', $this->sign2 ? $this->sign2 : $this->sign); // convert from hex to binary
    	
    	if (hash_hmac('md5', $data, $key) != $signature) {
    		throw new SecurityException('Response signature mismatch');
    	}
    }
}