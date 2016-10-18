<?php

namespace Mango\Bundle\JsonApiBundle\Serializer;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\MetadataFactory;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

/**
 * JsonApi Deserialization Visitor.
 */
class JsonApiDeserializationVisitor extends JsonDeserializationVisitor
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    protected $includedResources = [];

    public function __construct(
        PropertyNamingStrategyInterface $namingStrategy,
        ObjectConstructorInterface $objectConstructor,
        MetadataFactory $metadataFactory
    )
    {
        parent::__construct($namingStrategy, $objectConstructor);
        $this->metadataFactory = $metadataFactory;
    }

    public function visitProperty(PropertyMetadata $metadata, $data, Context $context)
    {
        $obj = parent::visitProperty($metadata, $data, $context);

        return $obj;
    }

}
