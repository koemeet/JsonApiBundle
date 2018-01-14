<?php

/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Fixtures;

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
}
