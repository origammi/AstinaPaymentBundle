<?php

namespace Astina\Bundle\PaymentBundle\Provider;

use Astina\Bundle\PaymentBundle\Provider\TransactionInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base implementation for a Transaction. ORM annotated as mapped superclass so
 * its extending classes could be used as domain entities
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractTransaction implements TransactionInterface
{
	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private $transactionId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $transactionToken;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	private $amount;
	
	/**
	 * @ORM\Column(type="string", length=3)
	 */
	private $currency;
	
	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private $reference;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	private $paymentMethod;

    /**
	 * @ORM\Column(type="string", length=50)
	 */
	private $providerName;

	private $cardNumber;
	
	/**
     * @ORM\Column(type="string", length=16)
	 */
	private $maskedCardNumber;
	
    /**
     * @ORM\Column(type="string", length=50)
     */
	private $cardAlias;
	
    /**
     * @ORM\Column(type="string", length=2)
     */
	private $expMonth;
	
    /**
     * @ORM\Column(type="string", length=4)
     */
	private $expYear;
	
    /**
     * @ORM\Column(type="string", length=5)
     */
	private $cvv;
	
	private $useAlias;
	
    /**
     * @ORM\Column(type="string", length=2)
     */
	private $language;
	
	private $authorizationCode;
	
	private $issuerAuthorizationCode;
	
	private $responseCode;
	
	private $responseMessage;
	
	private $status;
	
	private $requestType;

    private $payerId;

    public function setTransactionId($transactionId)
    {
    	$this->transactionId = $transactionId;
    }
    
    public function getTransactionId()
    {
    	return $this->transactionId;
    }
    
    public function setAmount($amount)
    {
    	$this->amount = $amount;
    }
    
    public function getAmount()
    {
    	return $this->amount;
    }
    
    public function setCurrency($currency)
    {
    	$this->currency = $currency;
    }
    
    public function getCurrency()
    {
    	return $this->currency;
    }
    
    public function setReference($reference)
    {
    	$this->reference = $reference;
    }
    
    public function getReference()
    {
    	return $this->reference;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }
        
    public function setPaymentMethod($paymentMethod)
    {
    	$this->paymentMethod = $paymentMethod;
    }
    
    public function getPaymentMethod()
    {
    	return $this->paymentMethod;
    }

    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    public function getProviderName()
    {
        return $this->providerName;
    }

    public function setCardNumber($cardNumber)
    {
    	$this->cardNumber = $cardNumber;
    }
    
    public function getCardNumber()
    {
    	return $this->cardNumber;
    }
    
    public function setMaskedCardNumber($maskedCardNumber)
    {
    	$this->maskedCardNumber = $maskedCardNumber;
    }
    
    public function getMaskedCardNumber()
    {
    	return $this->maskedCardNumber;
    }
    
    public function setCardAlias($cardAlias)
    {
    	$this->cardAlias = $cardAlias;
    }
    
    public function getCardAlias()
    {
    	return $this->cardAlias;
    }
    
    public function setExpMonth($expMonth)
    {
    	$this->expMonth = $expMonth;
    }
    
    public function getExpMonth()
    {
    	return $this->expMonth;
    }
    
    public function setExpYear($expYear)
    {
    	$this->expYear = $expYear;
    }
    
    public function getExpYear()
    {
    	return $this->expYear;
    }
    
    public function setCvv($cvv)
    {
    	$this->cvv = $cvv;
    }
    
    public function getCvv()
    {
    	return $this->cvv;
    }
    
    public function setUseAlias($useAlias)
    {
    	$this->useAlias = $useAlias;
    }
    
    public function getUseAlias()
    {
    	return $this->useAlias;
    }
    
    public function setLanguage($language)
    {
    	$this->language = $language;
    }
    
    public function getLanguage()
    {
    	return $this->language;
    }
    
    public function setAuthorizationCode($authorizationCode)
    {
    	$this->authorizationCode = $authorizationCode;
    }
    
    public function getAuthorizationCode()
    {
    	return $this->authorizationCode;
    }
    
    public function setIssuerAuthorizationCode($issuerAuthorizationCode)
    {
    	$this->issuerAuthorizationCode = $issuerAuthorizationCode;
    }
    
    public function getIssuerAuthorizationCode()
    {
    	return $this->issuerAuthorizationCode;
    }
    
    public function setResponseCode($responseCode)
    {
    	$this->responseCode = $responseCode;
    }
    
    public function getResponseCode()
    {
    	return $this->responseCode;
    }
    
    public function setResponseMessage($responseMessage)
    {
    	$this->responseMessage = $responseMessage;
    }
    
    public function getResponseMessage()
    {
    	return $this->responseMessage;
    }
    
    public function setStatus($status)
    {
    	$this->status = $status;
    }
    
    public function getStatus()
    {
    	return $this->status;
    }
    
    public function setRequestType($requestType)
    {
    	$this->requestType = $requestType;
    }
    
    public function getRequestType()
    {
    	return $this->requestType;
    }

    function setPayerId($payerId)
    {
        $this->payerId = $payerId;
    }

    function getPayerId()
    {
        return $this->payerId;
    }

    public function setTransactionToken($transactionToken)
    {
        $this->transactionToken = $transactionToken;
    }

    public function getTransactionToken()
    {
        return $this->transactionToken;
    }
}