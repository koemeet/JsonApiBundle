<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Construction;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\VisitorInterface;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\MetadataFactory as JsonApiMetadataFactory;
use Metadata\MetadataFactoryInterface;

/**
 * 
 */
class UnserializeJsonApiConstructor extends UnserializeObjectConstructor
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $fallbackConstructor;

    /**
     * @var JsonApiMetadataFactory
     */
    protected $jsonapiMetadataFactory;

    /**
     * @var array
     */
    protected $repository;

    /**
     * @param ObjectConstructorInterface $fallbackConstructor
     * @param JsonApiMetadataFactory $jsonapiMetadataFactory
     */
    public function __construct(ObjectConstructorInterface $fallbackConstructor, JsonApiMetadataFactory $jsonapiMetadataFactory)
    {
        $this->fallbackConstructor = $fallbackConstructor;
        $this->jsonapiMetadataFactory = $jsonapiMetadataFactory;
    }

    public function construct(VisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context)
    {
        $object = $this->findObject($metadata, $type);

        if ($object) {
            return $object;
        }

        $object = $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);

        

        return $object;
    }

    /**
     * @param ClassMetadata $metadata
     * @param array $data
     * @return object|null
     */
    protected function findObject(ClassMetadata $metadata, $data)
    {
        $jsonapiClassMetadata = $this->jsonapiMetadataFactory->getMetadataForResource($data['type']);
        $resourceId = $data['id'];
        $resourceType = $metadata->getResource()->getType();
        
        if (isset($this->repository[$resourceType][$resourceId])) {
            return $this->repository[$resourceType][$resourceId];
        }

        return null;
    }
}
