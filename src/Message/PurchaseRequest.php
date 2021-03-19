<?php

namespace Omnipay\Moneris\Message;

use Omnipay\Common\Exception\InvalidRequestException;

class PurchaseRequest extends AbstractRequest
{
    /**
     * CVD value is deliberately bypassed or is not provided by the merchant.
     *
     * @var int
     * @see https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const CVD_BYPASSED = 0;

    /**
     * CVD value is present.
     *
     * @var int
     * @see https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const CVD_PRESENT = 1;

    /**
     * CVD value is on the card, but is illegible.
     *
     * @var int
     * @see https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const CVD_ILLEGIBLE = 2;

    /**
     * Cardholder states that the card has no CVD imprint.
     *
     * @var int
     * @see https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const NO_CVD = 9;

    public function getData()
    {
        $data = null;

        $this->validate('orderNumber', 'cryptType', 'amount', 'paymentMethod', 'description');

        $paymentMethod = $this->getPaymentMethod();

        switch ($paymentMethod) {
            case 'payment_profile':
                $this->validate('cardReference');

                $request = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><request></request>');
                $request->addChild('store_id', $this->getMerchantId());
                $request->addChild('api_token', $this->getMerchantKey());

                $res_purchase_cc = $request->addChild('res_purchase_cc');
                $res_purchase_cc->addChild('data_key', $this->getCardReference());
                $res_purchase_cc->addChild('order_id', $this->getOrderNumber());
                $res_purchase_cc->addChild('cust_id', 'Transaction_'.$this->getOrderNumber());
                $res_purchase_cc->addChild('amount', $this->getAmount());
                $res_purchase_cc->addChild('crypt_type', $this->getCryptType());

                $data = $request->asXML();
                break;

            case 'card':
                $this->validate('card');

                $request = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><request></request>');
                $request->addChild('store_id', $this->getMerchantId());
                $request->addChild('api_token', $this->getMerchantKey());

                $card = $this->getCard();

                $purchase = $request->addChild('purchase');
                $purchase->addChild('pan', $card->getNumber());
                $purchase->addChild('expdate', $card->getExpiryDate('ym'));
                $purchase->addChild('order_id', $this->getOrderNumber());
                $purchase->addChild('cust_id', 'Transaction_'.$this->getOrderNumber());
                $purchase->addChild('amount', $this->getAmount());
                $purchase->addChild('crypt_type', $this->getCryptType());
                $purchase->addChild('dynamic_descriptor', $this->getDescription());

                $cvd_info = $purchase->addChild('cvd_info');
                $cvd_info->addChild('cvd_indicator', self::CVD_PRESENT);
                $cvd_info->addChild('cvd_value', $card->getCvv());

                $data = $request->asXML();
                break;

            // Todo: token payment

            default:
                throw new InvalidRequestException('Invalid payment method');
                break;
        }

        return preg_replace('/\n/', '', $data);
    }
}
