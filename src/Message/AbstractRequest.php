<?php

namespace  Omnipay\Moneris\Message;

use Omnipay\Common\Exception\InvalidRequestException;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    const MAIL_ORDER_TELEPHONE_ORDER_SINGLE = 1;
    const MAIL_ORDER_TELEPHONE_ORDER_RECURRING = 2;
    const MAIL_ORDER_TELEPHONE_ORDER_INSTALMENT = 3;
    const MAIL_ORDER_TELEPHONE_ORDER_UNKNOWN_CLASSIFICATION = 4;
    const AUTHENTICATED_E_COMMERCE_TRANSACTION_VBV = 5;
    const NON_AUTHENTICATED_E_COMMERCE_TRANSACTION_VBV = 6;
    const SSL_ENABLED_MERCHANT = 7;

    /**
     * Allowable values for the e-commerce transaction category being processed
     *
     * @var array
     * @see https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const ECOMMERCE_INDICATORS = array(
        self::MAIL_ORDER_TELEPHONE_ORDER_SINGLE,
        self::MAIL_ORDER_TELEPHONE_ORDER_RECURRING,
        self::MAIL_ORDER_TELEPHONE_ORDER_INSTALMENT,
        self::MAIL_ORDER_TELEPHONE_ORDER_UNKNOWN_CLASSIFICATION,
        self::AUTHENTICATED_E_COMMERCE_TRANSACTION_VBV,
        self::NON_AUTHENTICATED_E_COMMERCE_TRANSACTION_VBV,
        self::SSL_ENABLED_MERCHANT
    );

    public $testEndpoint = 'https://esqa.moneris.com:443/gateway2/servlet/MpgRequest';
    public $liveEndpoint = 'https://www3.moneris.com:443/gateway2/servlet/MpgRequest';

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getMerchantKey()
    {
        return $this->getParameter('merchantKey');
    }

    public function setMerchantKey($value)
    {
        return $this->setParameter('merchantKey', $value);
    }

    public function getCryptType()
    {
        return $this->getParameter('cryptType');
    }

    public function setCryptType($value)
    {
        return $this->setParameter('cryptType', $value);
    }

    public function getPaymentMethod()
    {
        return $this->getParameter('paymentMethod');
    }

    public function setPaymentMethod($value)
    {
        return $this->setParameter('paymentMethod', $value);
    }

    public function getPaymentProfile()
    {
        return $this->getParameter('paymentProfile');
    }

    public function setPaymentProfile($value)
    {
        return $this->setParameter('paymentProfile', $value);
    }

    public function getOrderNumber()
    {
        return $this->getParameter('orderNumber');
    }

    public function setOrderNumber($value)
    {
        return $this->setParameter('orderNumber', $value);
    }

    protected function getHttpMethod()
    {
        return 'POST';
    }

    /**
     * Validate the request.
     *
     * @param string ... a variable length list of required parameters
     * @throws InvalidRequestException
     * @see Omnipay\Common\ParametersTrait::validate()
     */
    public function validate(...$args)
    {
        foreach ($args as $key) {
            $value = $this->parameters->get($key);

            switch ($key) {
                case 'orderNumber':
                    if (! isset($value)) {
                        throw new InvalidRequestException("The $key parameter is required");
                    } elseif (strlen($value) > 50) {
                        throw new InvalidRequestException("The $key parameter cannot be longer than 50 characters");
                    }
                    break;

                case 'cryptType':
                    if (! isset($value)) {
                        throw new InvalidRequestException("The $key parameter is required");
                    } elseif (! in_array($value, self::ECOMMERCE_INDICATORS)) {
                        throw new InvalidRequestException("The $key is invalid");
                    }
                    break;

                case 'description':
                    if (strlen($value) > 20) {
                        throw new InvalidRequestException("The $key parameter cannot be longer than 20 characters");
                    }
                    break;

                default:
                    if (! isset($value)) {
                        throw new InvalidRequestException("The $key parameter is required");
                    }
                    break;
            }
        }
    }

    public function sendData($data)
    {
        $headers = [
            'Content-Type' => 'application/xml',
        ];

        $httpResponse = $this->httpClient->request($this->getHttpMethod(), $this->getEndpoint(), $headers, $data);

        try {
            $xmlResponse = simplexml_load_string($httpResponse->getBody()->getContents());
        } catch (\Exception $e) {
            $xmlResponse = (string) $httpResponse->getBody(true);
        }

        return $this->response = new Response($this, $xmlResponse);
    }
}
