<?php

namespace Omnipay\Moneris\Message;

use Omnipay\Common\Exception\InvalidRequestException;

class VoidRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('transactionReference');

        try {
            $transactionReference = simplexml_load_string($this->getTransactionReference());
        } catch (\Exception $e) {
            throw new InvalidRequestException('Invalid transaction reference');
        }

        $transactionReceipt = $transactionReference->receipt;

        $request = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><request></request>');
        $request->addChild('store_id', $this->getMerchantId());
        $request->addChild('api_token', $this->getMerchantKey());

        $refund = $request->addChild('completion');
        $refund->addChild('order_id', $transactionReceipt->ReceiptId);
        $refund->addChild('comp_amount', '0.00');
        $refund->addChild('txn_number', $transactionReceipt->TransID);
        $refund->addChild('crypt_type', $this->getCryptType());
        $refund->addChild('cust_id', $transactionReceipt->ReferenceNum);
        $refund->addChild('dynamic_descriptor', 'void');

        $data = $request->asXML();

        return preg_replace('/\n/', ' ', $data);
    }
}
