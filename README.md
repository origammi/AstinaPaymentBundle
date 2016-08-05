AstinaPaymentBundle
===================

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/efe39f01-ae60-49e0-afef-129d4b03b527/mini.png)](https://insight.sensiolabs.com/projects/efe39f01-ae60-49e0-afef-129d4b03b527)

Datatrans Provider
------------------

Service configuration:

    <service id="astina_payment.provider" class="Astina\PaymentBundle\Provider\Datatrans\Provider">
        <argument>%astina_payment.datatrans.merchantid%</argument>
        <argument>%astina_payment.datatrans.serviceurl%</argument>
        <argument>%astina_payment.datatrans.authorizexmlurl%</argument>
        <argument>%astina_payment.datatrans.capturexmlurl%</argument>
        <argument>%astina_payment.datatrans.sign%</argument>
        <argument>%astina_payment.datatrans.sign2%</argument>
        <argument type="service" id="logger" />
    </service>

Paypal Provider
------------------

The Paypal provider is using the NVP API. See the [documentation](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_NVPAPIOverview) for details.

The following API methods are implemented:

- [SetExpressCheckout](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_SetExpressCheckout) in `createPaymentUrl()`
- [GetExpressCheckoutDetails](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetExpressCheckoutDetails) in `createTransactionFromRequest()`
- [DoExpressCheckoutPayment](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoExpressCheckoutPayment) in `captureTransaction()`

Service configuration:

    <service id="astina_payment.provider" class="Astina\PaymentBundle\Provider\Paypal\Provider">
        <argument>%astina_payment.paypal.api_username%</argument>
        <argument>%astina_payment.paypal.api_password%</argument>
        <argument>%astina_payment.paypal.api_signature%</argument>
        <argument>%astina_payment.paypal.api_endpoint%</argument>
        <argument>%astina_payment.paypal.paypal_url%</argument>
        <argument>%astina_payment.paypal.subject%</argument>
        <argument type="service" id="logger" />
        <argument>%astina_payment.paypal.version%</argument> <!-- optional, defaults to 53.0 -->
    </service>

Saferpay Provider
------------------

The Saferpay provider is using the HTTPS API (V4.1.6).

Documenation: https://astina.atlassian.net/wiki/download/attachments/3932162/Saferpay+Payment+Page+V4.1.6+EN.pdf

Service Configuration:

    <service id="astina_payment.provider" class="Astina\PaymentBundle\Provider\Saferpay\Provider">
        <argument>%astina_payment.saferpay.endpoint%</argument>
        <argument>%astina_payment.saferpay.accountId%</argument>
        <argument>%astina_payment.saferpay.vtconfig%</argument> <!-- optional -->
        <argument type="service" id="logger" />
    </service>

Computop Provider
-----------------
Only authorization is implemented for now.

    <service id="astina_payment.provider" class="Astina\Bundle\PaymentBundle\Provider\Computop\Provider">
        <argument>[merchant id]</argument>
        <argument>[password]</argument>
        <argument>[hmac key]</argument>
        <argument>[testing mode true|false]</argument>
    </service>

Updating to version 2.0
-----------------
This release introduces an $environment variable for HttpsSaferpayEndpoint.php. If the environment is 'test', the payment information will be sent to the testing API 'https://test.saferpay.com/hosting/'.

To adapt the environment variable in your project, follow these steps:

- In your composer.json, make sure that version 2.0.x is used: "astina/payment-bundle": "~2.0"

- In your services.xml where you define the SaferpayEndpoint service, add the environment as argument:
 ```<service id="astina_payment.saferpay.endpoint" class="%astina_payment.saferpay.endpoint.class%"> <argument type="service" id="logger" /> <argument>%astina_payment.saferpay.accountId%</argument> <argument>%astina_payment.saferpay.password%</argument> <argument>%astina_payment.saferpay.environment%</argument> </service>```

- In your parameters.yml, set the environment to either test or production.

- In your parameters.yml.dist, don't forget to set the default value astina_payment.saferpay.environment: test.
