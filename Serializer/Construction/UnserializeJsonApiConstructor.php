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
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;

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
     * 
     */
    public function __construct(ObjectConstructorInterface $fallbackConstructor, JsonApiMetadataFactory $jsonapiMetadataFactory)
    {
        $this->fallbackConstructor = $fallbackConstructor;
        $this->jsonapiMetadataFactory = $jsonapiMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function construct(VisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context)
    {
        $jsonapiClassMetadata = $this->jsonapiMetadataFactory->getMetadataForClass($metadata->name);

        if (!$jsonapiClassMetadata || !$jsonapiClassMetadata->getResource()) {
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        $object = $this->findObject($jsonapiClassMetadata, $data);

        if ($object) {
            return $object;
        }

        $object = $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        
        $this->storeObject($jsonapiClassMetadata, $object, $data);

        return $object;
    }

    protected function storeObject(JsonApiClassMetadata $jsonapiClassMetadata, $object, array $data)
    {
        $idField = $jsonapiClassMetadata->getIdField();
        if (!isset($data[$idField])) {
            return null;
        }

        $resourceId = $data[$idField];
        $resourceType = $jsonapiClassMetadata->getResource()->getType();

        $this->repository[$resourceType][$resourceId] = $object;
    }

    /**
     * @return object|null
     */
    protected function findObject(JsonApiClassMetadata $jsonapiClassMetadata, array $data)
    {
        $idField = $jsonapiClassMetadata->getIdField();
        if (!isset($data[$idField])) {
            return null;
        }

        $resourceId = $data[$idField];
        $resourceType = $jsonapiClassMetadata->getResource()->getType();

        if (isset($this->repository[$resourceType][$resourceId])) {
            return $this->repository[$resourceType][$resourceId];
        }

        return null;
    }
}
