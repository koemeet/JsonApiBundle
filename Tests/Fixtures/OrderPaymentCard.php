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
 * @JsonApi\Resource(type="order/payment-card", showLinkSelf=false)
 */
class OrderPaymentCard extends OrderPaymentAbstract
{
    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("type")
     */
    public function getType()
    {
        return parent::TYPE_CARD;
    }
}
