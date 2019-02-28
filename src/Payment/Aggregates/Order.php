<?php

namespace Mundipagg\Core\Payment\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Payment\Aggregates\Payments\AbstractPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\SavedCreditCardPayment;
use Mundipagg\Core\Payment\Traits\WithCustomerTrait;

final class Order extends AbstractEntity
{
    use WithCustomerTrait;

    /** @var string */
    private $code;
    /** @var Item[] */
    private $items;
    /** @var null|Shipping */
    private $shipping;
    /** @var AbstractPayment[] */
    private $payments;
    /** @var boolean */
    private $closed;
    /** @var boolean */
    private $antifraudEnabled;

    public function __construct()
    {
        $this->payments = [];
        $this->items = [];
        $this->closed = true;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping|null $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return AbstractPayment[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    public function addPayment(AbstractPayment $payment)
    {
        $this->validate($payment);

        $this->payments[] = $payment;

    }

    private function validate(AbstractPayment $payment)
    {
        $paymentClass = get_class($payment);
        $paymentClass = explode ('\\', $paymentClass);
        $paymentClass = end($paymentClass);
        $paymentValidator = "validate$paymentClass";
        if (method_exists($this, $paymentValidator)) {
            $this->$paymentValidator($payment);
        }
    }

    private function validateSavedCreditCardPayment(SavedCreditCardPayment $payment)
    {
        if ($this->customer === null) {
            throw new \Exception(
                'To use a saved credit card payment in an order ' .
                'you must add a customer to it.',
                400
            );
        }

        $customerId = $this->customer->getMundipaggId();
        if ($customerId === null) {
            throw new \Exception(
                'You can\'t use a saved credit card of a fresh new customer',
                400
            );
        }

        if (!$customerId->equals($payment->getOwner())) {
            throw new \Exception(
                'The saved credit card informed doesn\'t belong to the informed customer.',
                400
            );
        }
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param bool $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * @return bool
     */
    public function isAntifraudEnabled()
    {
        return $this->antifraudEnabled;
    }

    /**
     * @param bool $antifraudEnabled
     */
    public function setAntifraudEnabled($antifraudEnabled)
    {
        $this->antifraudEnabled = $antifraudEnabled;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->customer = $this->getCustomer();
        $obj->code = $this->getCode();
        $obj->items = $this->getItems();

        $shipping = $this->getShipping();
        if ($shipping !== null) {
            $obj->shipping = $this->getShipping();
        }

        $obj->payments = $this->getPayments();
        $obj->closed = $this->isClosed();
        $obj->antifraudEnabled = $this->isAntifraudEnabled();

        return $obj;
    }
}