<?php

namespace Omnipay\Moneris\Tests\Message;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Moneris\Message\PurchaseRequest;
use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    protected $request;

    protected function setUp()
    {
        parent::setUp();

        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function test_missing_payment_method_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 1,
            'cardReference' => 'FAKE_CARD_REFERENCE',
            'amount'        => 5.00,
        ]);

        $this->expectException(InvalidRequestException::class);
        $this->request->send();
    }

    public function test_an_invalid_payment_method_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'test',
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 1,
            'cardReference' => 'FAKE_CARD_REFERENCE',
            'amount'        => 5.00,
        ]);

        try {
            $this->request->send();
        } catch (InvalidRequestException $e) {
            $this->assertEquals('test', $this->request->getPaymentMethod());

            return;
        }

        $this->fail('Purchase with an invalid payment method did not throw an InvalidRequestException.');
    }

    public function test_missing_order_number_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'payment_profile',
            'cryptType'     => 1,
            'cardReference' => 'FAKE_CARD_REFERENCE',
            'amount'        => 5.00,
        ]);

        $this->expectException(InvalidRequestException::class);
        $this->request->send();
    }

    public function test_an_invalid_order_number_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'payment_profile',
            'orderNumber'   => '',
            'cryptType'     => 1,
            'cardReference' => 'FAKE_CARD_REFERENCE',
            'amount'        => 5.00,
        ]);

        try {
            $this->request->send();
        } catch (InvalidRequestException $e) {
            $this->assertEquals('', $this->request->getOrderNumber());

            return;
        }
        $this->fail('Purchase with an invalid order number did not throw an InvalidRequestException.');
    }

    public function test_missing_crypt_type_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'payment_profile',
            'orderNumber'   => 'XXXX-XXXX',
            'cardReference' => 'FAKE_CARD_REFERENCE',
            'amount'        => 5.00,
        ]);

        $this->expectException(InvalidRequestException::class);
        $this->request->send();
    }

    public function test_an_invalid_crypt_type_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'payment_profile',
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 0,
            'cardReference' => 'FAKE_CARD_REFERENCE',
            'amount'        => 5.00,
        ]);

        try {
            $this->request->send();
        } catch (InvalidRequestException $e) {
            $this->assertEquals(0, $this->request->getCryptType());

            return;
        }

        $this->fail('Purchase with an invalid crypt type did not throw an InvalidRequestException.');
    }

    public function test_missing_amount_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'payment_profile',
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 1,
            'cardReference' => 'FAKE_CARD_REFERENCE',
        ]);

        $this->expectException(InvalidRequestException::class);
        $this->request->send();
    }

    public function test_an_invalid_amount_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'payment_profile',
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 1,
            'cardReference' => 'FAKE_CARD_REFERENCE',
            'amount'        => -5,
        ]);

        $this->expectException(InvalidRequestException::class);
        $this->request->send();
    }

    public function test_an_invalid_description_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'payment_profile',
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 1,
            'cardReference' => 'FAKE_CARD_REFERENCE',
            'amount'        => 5.00,
            'description'   => "ZGL5Dp0htqaKRzfeIOiVJm",    // randomly generated 22 character string
        ]);

        $this->expectException(InvalidRequestException::class);
        $this->request->send();
    }

    public function test_missing_a_card_reference_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'payment_profile',
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 1,
            'amount'        => 5.00,
        ]);

        $this->expectException(InvalidRequestException::class);
        $this->request->send();
    }

    public function test_missing_a_card_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'card',
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 1,
            'amount'        => 5.00,
        ]);


        $this->expectException(InvalidRequestException::class);
        $this->request->send();
    }

    public function test_an_expired_card_should_throw_an_exception_for_the_purchase_request()
    {
        $this->request->initialize([
            'paymentMethod' => 'card',
            'orderNumber'   => 'XXXX-XXXX',
            'cryptType'     => 1,
            'amount'        => 5.00,
            'card'          => [
                'number'        => '4242424242424242',
                'expiryMonth'   => date('m'),
                'expiryYear'    => date('Y') - 1,
                'cvv'           => 123,
            ],
        ]);

        $this->expectException(InvalidCreditCardException::class);
        $this->request->send();
    }
}
