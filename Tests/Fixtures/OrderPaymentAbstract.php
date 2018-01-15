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
     *
     * @var string
     */
    private $id;

    /**
     * @JMS\Type("float")
     *
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    protected $type;

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
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * getType
     *
     * @return string
     */
    abstract public function getType();
}
