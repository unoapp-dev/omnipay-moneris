<?php

namespace Omnipay\Moneris;

use Omnipay\Common\AbstractGateway;

/**
 * Moneris Gateway
 * @link https://esqa.moneris.com/mpg/reports/transaction/index.php
 * @link https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/
 */

class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Moneris';
    }

    public function getDefaultParameters()
    {
        return [
            'sandboxEndPoint' => '',
            'productionEndPoint' => '',
            'merchantId' => '',
            'merchantKey' => '',
        ];
    }
    
    public function getSandboxEndPoint()
    {
        return $this->getParameter('sandboxEndPoint');
    }
    
    public function setSandboxEndPoint($value)
    {
        return $this->setParameter('sandboxEndPoint', $value);
    }
    
    public function getProductionEndPoint()
    {
        return $this->getParameter('productionEndPoint');
    }
    
    public function setProductionEndPoint($value)
    {
        return $this->setParameter('productionEndPoint', $value);
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

    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Moneris\Message\CreateCardRequest', $parameters);
    }

    public function deleteCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Moneris\Message\DeleteCardRequest', $parameters);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Moneris\Message\PurchaseRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Moneris\Message\RefundRequest', $parameters);
    }
    
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Moneris\Message\AuthorizeRequest', $parameters);
    }
    
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Moneris\Message\CaptureRequest', $parameters);
    }
}

