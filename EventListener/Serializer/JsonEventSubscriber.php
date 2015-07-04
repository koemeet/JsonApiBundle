<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\EventListener\Serializer;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\VisitorInterface;
use Mango\Hateoas\Configuration\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class JsonEventSubscriber implements EventSubscriberInterface
{
    /**
     * Keep track of all included relationships, so that we do not duplicate them
     *
     * @var array
     */
    protected $includedRelationships = array();

    /**
     * @var MetadataFactoryInterface
     */
    protected $hateoasMetadataFactory;

    /**
     * @var MetadataFactoryInterface
     */
    protected $jmsMetadataFactory;

    /**
     * @var PropertyNamingStrategyInterface
     */
    protected $namingStrategy;

    /**
     * @param MetadataFactoryInterface        $hateoasMetadataFactory
     * @param PropertyNamingStrategyInterface $namingStrategy
     */
    public function __construct(MetadataFactoryInterface $hateoasMetadataFactory, MetadataFactoryInterface $jmsMetadataFactory, PropertyNamingStrategyInterface $namingStrategy)
    {
        $this->hateoasMetadataFactory = $hateoasMetadataFactory;
        $this->jmsMetadataFactory = $jmsMetadataFactory;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'method' => 'onPostSerialize',
            ),
        );
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $visitor = $event->getVisitor();
        $object = $event->getObject();
        $context = $event->getContext();

        /** @var ClassMetadata $metadata */
        $metadata = $this->hateoasMetadataFactory->getMetadataForClass(get_class($object));

        /** @var \JMS\Serializer\Metadata\ClassMetadata $jmsMetadata */
        $jmsMetadata = $this->jmsMetadataFactory->getMetadataForClass(get_class($object));

        $propertyAccessor = PropertyAccess::createPropertyAccessor();


        if ($visitor instanceof JsonApiSerializationVisitor) {
            $this->prependData($visitor, array(
                'type' => $metadata->getResource()->getType()
            ));

            $relationshipsData = array();

            foreach ($metadata->getRelationships() as $relationship) {
                $relKey = $relationship->getName();

                if (!isset($jmsMetadata->propertyMetadata[$relKey])) {
                    continue;
                }

                $propertyMetadata = $jmsMetadata->propertyMetadata[$relKey];

                $translatedName = $this->namingStrategy->translateName($propertyMetadata);

                $rel =& $relationshipsData[$translatedName];
                $relData =& $rel['data'];

                $data = $propertyAccessor->getValue($object, $relKey);

                if (is_array($data) || $data instanceof \Traversable) {
                    $relData = array();
                    foreach ($data as $item) {
                        /** @var ClassMetadata $relMetadata */
                        $relMetadata = $this->hateoasMetadataFactory->getMetadataForClass(get_class($item));

                        $id = $propertyAccessor->getValue($item, 'id');

                        if ($relationship->isIncludedByDefault()) {
                            $this->includedRelationships[$relKey . ':' . $id] = $context->accept($item);
                        }

                        $relData[] = array(
                            'type' => $relMetadata->getResource()->getType(),
                            'id' => $id
                        );
                    }
                } else {
                    $relData = array();

                    /** @var ClassMetadata $relMetadata */
                    $relMetadata = $this->hateoasMetadataFactory->getMetadataForClass(get_class($data));

                    $id = $propertyAccessor->getValue($data, 'id');

                    if ($relationship->isIncludedByDefault()) {
                        // TODO: How do we check if the relationship is already in `data`?
                        //$this->includedRelationships[$relKey . ':' . $id] = $context->accept($data);
                    }

                    $relData[] = array(
                        'type' => $relMetadata->getResource()->getType(),
                        'id' => $id
                    );
                }
            }

            if (!empty($relationshipsData)) {
                $visitor->addData('relationships', $relationshipsData);
            }

            $root = (array)$visitor->getRoot();
            $root['included'] = array_values($this->includedRelationships);
            $visitor->setRoot($root);
        }
    }

    /**
     * Prepend some data
     *
     * @param VisitorInterface $visitor
     * @param array            $prependData
     */
    protected function prependData(VisitorInterface $visitor, array $prependData)
    {
        $refl = new \ReflectionObject($visitor);
        $property = $refl->getParentClass()->getParentClass()->getProperty('data');
        $property->setAccessible(true);

        $data = $property->getValue($visitor);
        $data = $prependData + $data;

        $property->setValue($visitor, $data);
    }
}