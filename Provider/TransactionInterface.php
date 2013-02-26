<?php

namespace Astina\Bundle\PaymentBundle\Provider;

/**
 * E-Payment transaction
 * 
 * @author $Author pkraeutli $
 * @version $Revision$, $Date$
 */
interface TransactionInterface
{
    function isStatusSuccess();

    /**
     * Authorization, Capture, ... depending on the provider
     */
    function setRequestType($requestType);
    
    function getRequestType();
    
    function getRequestTypeAuthorize();
    
    function getRequestTypeAuthorizeCapture();
    
    function getRequestTypeCapture();
    
	/**
	 * Unique transaction identifier. This is usually provided by the payment provider.
	 */
	function setTransactionId($transactionId);
    
    function getTransactionId();

    function setTransactionToken($transactionToken);

    function getTransactionToken();

	/**
	 * Transaction amount in cents or smallest available unit of the currency
	 */
	function setAmount($amount);
	
	function getAmount();
	
	/**
	 * ISO currency code
	 */
	function setCurrency($currency);
	
	function getCurrency();
	
	/**
	 * Reference number for the transaction
	 */
	function setReference($reference);
	
	function getReference();

    /**
     * Order description
     *
     * @param $description
     */
    function setDescription($description);

    function getDescription();
	
	/**
	 * Type of payment (e.g. Saferpay, Paypal, ...)
	 */
	function setPaymentMethod($paymentMethod);
	
	function getPaymentMethod();

    /**
	 * Provider name (e.g. card type like Visa, Mastercard, ...)
	 */
	function setProviderName($providerName);

	function getProviderName();

	/**
	 * Credit card number
	 */
	function setCardNumber($cardNumber);
	
	function getCardNumber();
	
    /**
     * Masked credit card number
     */
    function setMaskedCardNumber($maskedCardNumber);
    
    function getMaskedCardNumber();
	
	/**
	 * Credit card number alias
	 */
	function setCardAlias($cardAlias);
	
	function getCardAlias();
	
	/**
	 * Expiry month of the card
	 */
	function setExpMonth($expMonth);
	
	function getExpMonth();
	
	/**
	 * Expiry year of the card
	 */
	function setExpYear($expYear);
	
	function getExpYear();
	
	/**
	 * CVV code
	 */
	function setCvv($cvv);
	
	function getCvv();
	
	/**
	 * Whether the card number or alias should be used
	 */
	function setUseAlias($useAlias);
	
	function getUseAlias();
	
	/**
	 * ISO-639 2 language code
	 */
	function setLanguage($language);
	
	function getLanguage();
	
	/**
	 * Transaction authorization code
	 */
	function setAuthorizationCode($authorizationCode);
	
	function getAuthorizationCode();
	
	/**
	 * Authorization code returned by credit card issuing bank
	 */
	function setIssuerAuthorizationCode($issuerAuthorizationCode);
	
	function getIssuerAuthorizationCode();
	
	/**
	 * Authorization response code
	 */
	function setResponseCode($responseCode);
	
	function getResponseCode();
	
	/**
	 * Response message text
	 */
	function setResponseMessage($responseMessage);
	
    function getResponseMessage();
    
    /**
     * Transaction status
     */
    function setStatus($status);
    
    function getStatus();

    function setPayerId($payerId);

    function getPayerId();
}