<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;

/** @JsonApi\Resource(type="order", showLinkSelf=false) */
class Order
{
    /**
     * @JsonApi\Id()
     * @JMS\Type("string")
     */
    private $id;

    /** @JMS\Type("string") */
    private $email;

    /** @JMS\Type("string") */
    private $phone;

    /** @JMS\Type("string") */
    private $adminComments;

    /**
     * @JsonApi\Relationship(includeByDefault="true", showLinkSelf=false, showLinkRelated=false)
     * @JMS\Type("Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderAddress")
     *
     * @var OrderAddress
     */
    private $address;

    /**
     * @JsonApi\Relationship(includeByDefault="true", showLinkSelf=false, showLinkRelated=false)
     * @JMS\Type("Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderPaymentAbstract")
     *
     * @var OrderPaymentAbstract
     */
    private $payment;

    /**
     * @JsonApi\Relationship(includeByDefault="true", showLinkSelf=false, showLinkRelated=false)
     * @JMS\Type("array<Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderItem>")
     *
     * @var OrderItem[]
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getAdminComments()
    {
        return $this->adminComments;
    }

    /**
     * @param mixed $adminComments
     */
    public function setAdminComments($adminComments)
    {
        $this->adminComments = $adminComments;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return OrderPaymentAbstract
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param OrderPaymentAbstract $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}
