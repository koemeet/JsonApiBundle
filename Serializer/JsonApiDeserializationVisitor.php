<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\AdvancedNamingStrategyInterface;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

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
        if ($this->namingStrategy instanceof AdvancedNamingStrategyInterface) {
            $propertyName = $this->namingStrategy->getPropertyName($metadata, $context);
        } elseif ($this->namingStrategy instanceof PropertyNamingStrategyInterface) {
            $propertyName = $this->namingStrategy->translateName($metadata);
        } else {
            $propertyName = $metadata->name;
        }

        if ($metadata->name === 'id') {
            if (isset($data['id'])) {
                parent::visitProperty(
                    $metadata,
                    $data,
                    $context
                );
            } elseif (isset($data['data'])) {
                parent::visitProperty(
                    $metadata,
                    $data['data'],
                    $context
                );
            }
        } elseif (isset($data['data']['relationships'][$propertyName]) ||
            isset($data['relationships'][$propertyName])) { // TODO: add this property
            $included = isset($data['included']) ? $data['included'] : [];

            $visit = false;
            $relationship = [];
            if (array_key_exists('data', $data['data']['relationships'][$propertyName])) {
                $relationship = $data['data']['relationships'][$propertyName]['data'];
                $visit = true;
            } elseif (array_key_exists('data', $data['relationships'][$propertyName])) {
                $relationship = $data['relationships'][$propertyName]['data'];
                $visit = true;
            }

            $relationshipData = [];
            foreach ($included as $include) {
                if ($include['type'] === $relationship['type'] && $include['id'] === $relationship['id']) {
                    $relationshipData = $include;
                    break;
                }
            }

            if (!$relationshipData) {
                $relationshipData = $relationship;
            }

            if ($relationshipData || $visit) {
                parent::visitProperty(
                    $metadata,
                    [$propertyName => $relationshipData],
                    $context
                );
            }
        } elseif (isset($data['data']['attributes'])) {
            parent::visitProperty(
                $metadata,
                $data['data']['attributes'],
                $context
            );
        } elseif (isset($data['attributes'])) {
            parent::visitProperty(
                $metadata,
                $data['attributes'],
                $context
            );
        } else {
            parent::visitProperty(
                $metadata,
                [],
                $context
            );
        }
    }
}
