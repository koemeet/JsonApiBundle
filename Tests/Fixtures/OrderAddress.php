<?php

namespace Mango\Bundle\JsonApiBundle\Tests\Fixtures;

use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;
use JMS\Serializer\Annotation as JMS;

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