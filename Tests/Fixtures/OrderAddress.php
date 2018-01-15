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

/** @JsonApi\Resource(type="order/address", showLinkSelf=false) */
class OrderAddress
{
    /**
     * @JsonApi\Id()
     * @JMS\Type("string")
     */
    private $id;

    /** @JMS\Type("string") */
    private $street;

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
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }
}
