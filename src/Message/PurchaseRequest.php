<?php
/**
 * Moneris Purchase Request.
 */

namespace Omnipay\Moneris\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Moneris Purchase Request class.
 *
 * Moneris provides various payment related operations based on the data
 * submitted to their API. Use purchase for direct credit card payments
 * and customer profile payments.
 *
 * ### Example
 *
 * #### Initialize Gateway
 *
 * <code>
 *   // Create a gateway for the Moneris Gateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('Moneris');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'merchantId'       => 'MyMonerisStoreId',
 *       'merchantSecret'   => 'MyMonerisAPIToken',
 *       'cryptType'        => 7,
 *       'testMode'         => true, // Or false when you are ready for live transactions
 *   ));
 * </code>
 *
 * #### Direct Credit Card Payment
 *
 * This is for the use case where a customer has presented their
 * credit card details and you intend to use the Moneris gateway
 * for processing a transaction using that credit card data.
 *
 * <code>
 *   // Create a credit card object
 *   // DO NOT USE THESE CARD VALUES -- substitute your own
 *   // see the documentation in the class header.
 *   $card = new CreditCard(array(
 *       'firstName'            => 'Example',
 *       'lastName'             => 'User',
 *       'number'               => '4111111111111111',
 *       'expiryMonth'          => '01',
 *       'expiryYear'           => '2020',
 *       'cvv'                  => '123',
 *       'billingAddress1'      => '1 Scrubby Creek Road',
 *       'billingCountry'       => 'AU',
 *       'billingCity'          => 'Scrubby Creek',
 *       'billingPostcode'      => '4999',
 *       'billingState'         => 'QLD',
 *   ));
 *
 *   // Do a purchase transaction on the gateway
 *   try {
 *       $transaction = $gateway->purchase(array(
 *           'orderNumber'   => 'XXXX-XXXX',
 *           'amount'        => '10.00',
 *           'description'   => 'This is a test purchase transaction.',
 *           'card'          => $card,
 *       ));
 *       $response = $transaction->send();
 *       $data = $response->getData();
 *       echo "Gateway purchase response data == " . print_r($data, true) . "\n";
 *
 *       if ($response->isSuccessful()) {
 *           echo "Purchase transaction was successful!\n";
 *       }
 *   } catch (\Exception $e) {
 *       echo "Exception caught while attempting authorize.\n";
 *       echo "Exception type == " . get_class($e) . "\n";
 *       echo "Message == " . $e->getMessage() . "\n";
 *   }
 * </code>
 *
 * @link https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
 */
class PurchaseRequest extends AbstractRequest
{
    /**
     * CVD value is deliberately bypassed or is not provided by the merchant.
     *
     * @var int
     * @link https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const CVD_BYPASSED = 0;

    /**
     * CVD value is present.
     *
     * @var int
     * @link https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const CVD_PRESENT = 1;

    /**
     * CVD value is on the card, but is illegible.
     *
     * @var int
     * @link https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const CVD_ILLEGIBLE = 2;

    /**
     * Cardholder states that the card has no CVD imprint.
     *
     * @var int
     * @link https://developer.moneris.com/en/Documentation/NA/E-Commerce%20Solutions/API/Purchase
     */
    const NO_CVD = 9;

    public function getData()
    {
        $data = null;

        $this->validate('paymentMethod', 'orderNumber', 'cryptType', 'amount', 'description');

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

                if ($this->getDescription()) {
                    $res_purchase_cc->addChild('dynamic_descriptor', $this->getDescription());
                }

                $data = $request->asXML();
                break;

            case 'card':
                $this->validate('card');

                $card = $this->getCard();
                $card->validate();

                $request = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><request></request>');
                $request->addChild('store_id', $this->getMerchantId());
                $request->addChild('api_token', $this->getMerchantKey());

                $purchase = $request->addChild('purchase');
                $purchase->addChild('pan', $card->getNumber());
                $purchase->addChild('expdate', $card->getExpiryDate('ym'));
                $purchase->addChild('order_id', $this->getOrderNumber());
                $purchase->addChild('cust_id', 'Transaction_'.$this->getOrderNumber());
                $purchase->addChild('amount', $this->getAmount());
                $purchase->addChild('crypt_type', $this->getCryptType());

                if ($this->getDescription()) {
                    $purchase->addChild('dynamic_descriptor', $this->getDescription());
                }

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
