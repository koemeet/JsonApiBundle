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

/**
 * @JMS\Discriminator(
 *   field = "type",
 *   disabled = true,
 *   map = {
 *      "cash": "Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderPaymentCash",
 *      "card": "Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderPaymentCard"
 *   }
 * )
 *
 * @JsonApi\Resource(type="order/payment", showLinkSelf=false)
 */
abstract class OrderPaymentAbstract
{
    const TYPE_CASH = 'cash';
    const TYPE_CARD = 'card';

    /**
     * @JsonApi\Id()
     * @JMS\Type("string")
     */
    private $id;

    /** @JMS\Type("float") */
    private $amount;

    protected $type;

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
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * getType
     *
     * @return string
     */
    abstract public function getType();
}
