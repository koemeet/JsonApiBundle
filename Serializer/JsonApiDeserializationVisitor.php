<?php

namespace Mango\Bundle\JsonApiBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * JsonApi Deserialization Visitor.
 */
class JsonApiDeserializationVisitor extends JsonDeserializationVisitor
{
  protected $includedResources = [];

  protected $root;

  public function prepare($data)
  {
    $data = parent::prepare($data);

    $this->root = $data;

    return $data;
  }

  public function visitProperty(PropertyMetadata $metadata, $data, Context $context)
  {
    if ($metadata->name === 'id') {
      if (isset($data['id'])) {
        parent::visitProperty($metadata, $data, $context);
      } elseif (isset($data['data'])) {
        parent::visitProperty($metadata, $data['data'], $context);
      }
    } elseif(isset($data['data']['relationships'][$metadata->name]) || isset($data['relationships'][$metadata->name])) { // TODO: add this property

      $included = $data['included'];
      $relationship = $data['data']['relationships'][$metadata->name]['data'];

      $relationshipData = [];
      foreach ($included as $include) {
        if ($include['type'] === $relationship['type'] && $include['id'] === $relationship['id']) {
          $relationshipData = $include;
          break;
        }
      }

      if ($relationshipData) {
        parent::visitProperty($metadata, [$metadata->name => $relationshipData], $context);
      }
    } elseif (isset($data['data']['attributes'])) {
      parent::visitProperty($metadata, $data['data']['attributes'], $context);
    } elseif (isset($data['attributes'])) {
      parent::visitProperty($metadata, $data['attributes'], $context);
    }
  }
}