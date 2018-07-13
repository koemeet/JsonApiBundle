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

/**
 * @JsonApi\Resource(type="order", showLinkSelf=false)
 *
 * @author Ruslan Zavacky <ruslan.zavacky@gmail.com>
 */
class Order
{
    /**
     * @JsonApi\Id()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $email;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $phone;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $adminComments;

    /**
     * @JsonApi\Relationship(includeByDefault=true, showLinkSelf=false, showLinkRelated=false)
     * @JMS\Type("Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderAddress")
     *
     * @var OrderAddress
     */
    private $address;

    /**
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date")
     *
     * @var \DateTime
     */
    private $orderDate;

    /**
     * @JsonApi\Relationship(includeByDefault=true, showLinkSelf=false, showLinkRelated=false)
     * @JMS\Type("Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderPaymentAbstract")
     *
     * @var OrderPaymentAbstract
     */
    private $payment;

    /**
     * @JsonApi\Relationship(includeByDefault=true, showLinkSelf=false, showLinkRelated=false)
     * @JMS\Type("array<Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderItem>")
     *
     * @var OrderItem[]
     */
    private $items;

    /**
     * @JsonApi\Relationship(includeByDefault=true, showLinkSelf=false, showLinkRelated=false)
     * @JMS\Type("ArrayCollection<Mango\Bundle\JsonApiBundle\Tests\Fixtures\GiftCoupon>")
     *
     * @var GiftCoupon[]
     */
    private $giftCoupons;

    /**
     * Order constructor
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->giftCoupons = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminComments()
    {
        return $this->adminComments;
    }

    /**
     * @param string $adminComments
     *
     * @return $this
     */
    public function setAdminComments($adminComments)
    {
        $this->adminComments = $adminComments;

        return $this;
    }

    /**
     * @return OrderAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * @param \DateTime $orderDate
     *
     * @return $this
     */
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
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
     *
     * @return $this
     */
    public function setPayment(OrderPaymentAbstract $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return ArrayCollection|OrderItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection|OrderItem[] $items
     *
     * @return $this
     */
    public function setItems(ArrayCollection $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return GiftCoupon[]
     */
    public function getGiftCoupons()
    {
        return $this->giftCoupons;
    }

    /**
     * @param OrderItem[] $giftCoupons
     *
     * @return $this
     */
    public function setGiftCoupons($giftCoupons)
    {
        $this->giftCoupons = $giftCoupons;

        return $this;
    }
}
