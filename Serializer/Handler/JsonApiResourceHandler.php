<?php

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiResource;
use Reconz\Bundle\DatalayerClientBundle\Model\Agent;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadata;

class JsonApiResourceHandler implements SubscribingHandlerInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $hateoasMetadataFactory;

    /**
     * @var MetadataFactoryInterface
     */
    protected $jmsMetadataFactory;
    
    /**
     * 
     */
    public function __construct(MetadataFactoryInterface $hateoasMetadataFactory, MetadataFactoryInterface $jmsMetadataFactory)
    {
        $this->hateoasMetadataFactory = $hateoasMetadataFactory;
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
        $type = ['name' => Agent::class, 'params' => []];
        $data = $this->processData($data, Agent::class);

        return $context->accept($data, $type);
    }

    private function processData(array $data, $resourceClassName)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $this->hateoasMetadataFactory->getMetadataForClass($resourceClassName);

        // if it has no json api metadata, skip it
        if (null === $metadata) {
            return;
        }
        $attributes = isset($data['attributes']) ? $data['attributes'] : null;

        $relationshipsData = isset($data['relationships']) ? $data['relationships'] : array();

        $jmsClassMetadata = $this->jmsMetadataFactory->getMetadataForClass($resourceClassName);

        foreach ($metadata->getRelationships() as $relationshipMeta) {
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
                $attributes[$metadata->getIdField()] = $id;
            }
        }

        return $attributes;
    }
}
