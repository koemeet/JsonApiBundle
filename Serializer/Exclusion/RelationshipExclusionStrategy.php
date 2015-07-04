<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Exclusion;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class RelationshipExclusionStrategy implements ExclusionStrategyInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $context)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        /** @var \Mango\Hateoas\Configuration\Metadata\ClassMetadata $metadata */
        $metadata = $this->metadataFactory->getMetadataForClass($property->class);

        foreach ($metadata->getRelationships() as $relationship) {
            if ($property->name === $relationship->getName()) {
                return true;
            }
        }
    }
}
