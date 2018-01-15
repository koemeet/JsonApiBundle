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
 * @JsonApi\Resource(type="order/address", showLinkSelf=false)
 *
 * @author Ruslan Zavacky <ruslan.zavacky@gmail.com>
 */
class OrderAddress
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
    private $street;

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
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }
}
