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

    protected $root;

    public function __construct(
        PropertyNamingStrategyInterface $namingStrategy,
        ObjectConstructorInterface $objectConstructor,
        MetadataFactory $metadataFactory
    )
    {
        
        parent::__construct($namingStrategy, $objectConstructor);
        $this->metadataFactory = $metadataFactory;
    }

    public function prepare($data)
    {
        $data = parent::prepare($data);

        $this->root = $data;
        
        return $data;
    }

    public function visitProperty(PropertyMetadata $metadata, $data, Context $context)
    {
        $obj = parent::visitProperty($metadata, $data, $context);

        return $obj;
    }
//
//    public function setNavigator(GraphNavigator $navigator)
//    {
//        parent::setNavigator($navigator);
//
//        if (isset($this->root['included'])) {
//            foreach ($this->root['included'] as $included) {
//                $includedObject = $navigator->accept($included, ['name' => JsonApiResource::class, 'params' => []], new \JMS\Serializer\DeserializationContext());
//                dump($includedObject);exit;
//            }
//        }
//    }
}
