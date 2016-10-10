<?php

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\MetadataFactory;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiResource;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadata;

class JsonApiResourceHandler implements SubscribingHandlerInterface
{
    /**
     * @var MetadataFactory
     */
    protected $jsonapiMetadataFactory;

    /**
     * @var MetadataFactoryInterface
     */
    protected $jmsMetadataFactory;
    
    /**
     * @param MetadataFactory $jsonapiMetadataFactory
     * @param MetadataFactoryInterface $jmsMetadataFactory
     */
    public function __construct(MetadataFactory $jsonapiMetadataFactory, MetadataFactoryInterface $jmsMetadataFactory)
    {
        $this->jsonapiMetadataFactory = $jsonapiMetadataFactory;
        $this->jmsMetadataFactory = $jmsMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => JsonApiResource::class,
                'method' => 'deserializeResource'
            ]
        ];
    }

    public function deserializeResource(
        JsonDeserializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    )
    {
        $resourceName = $data['type'];
        $classMetadata = $this->jsonapiMetadataFactory->getMetadataForResource($resourceName);

        if (null === $classMetadata) {
            return $visitor->visitArray($data, ['name' => 'array', 'params' => []], $context);
        }

        $type = ['name' => $classMetadata->name, 'params' => []];
        $data = $this->processData($data, $classMetadata);

        return $context->accept($data, $type);
    }

    private function processData(array $data, ClassMetadata $classMetadata)
    {
        $attributes = isset($data['attributes']) ? $data['attributes'] : null;

        $relationshipsData = isset($data['relationships']) ? $data['relationships'] : array();

        $jmsClassMetadata = $this->jmsMetadataFactory->getMetadataForClass($classMetadata->name);

        foreach ($classMetadata->getRelationships() as $relationshipMeta) {
            $relationshipName = $relationshipMeta->getName();

            $jmsPropertyMetadata = isset($jmsClassMetadata->propertyMetadata[$relationshipName]) ? $jmsClassMetadata->propertyMetadata[$relationshipName] : null;
            /* @var $jmsPropertyMetadata PropertyMetadata */

            $serializedName = $jmsPropertyMetadata->serializedName ?: $relationshipName;
            if (isset($relationshipsData[$serializedName])) {
                $relationshipData = $relationshipsData[$serializedName];

                if ($this->isSequentialArray($relationshipData['data'])) {
                    foreach ($relationshipData['data'] as $relationship) {
                        $relationshipId = $relationship['id'];
                        $relationshipType = $relationship['type'];
                        $attributes[$serializedName][] = ['id' => $relationshipId];
                    }
                } else {
                    $relationshipId = $relationshipData['data']['id'];
                    $relationshipType = $relationshipData['data']['type'];
                    $attributes[$serializedName] = ['id' => $relationshipId];
                }
            }
        }

        if (isset($data['id'])) {
           $id = $data['id'];

            if (null !== $id) {
                $attributes[$classMetadata->getIdField()] = $id;
            }
        }

        return $attributes;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function isSequentialArray($data)
    {
        return is_array($data) && array_keys($data) === range(0, count($data) - 1);
    }
}
