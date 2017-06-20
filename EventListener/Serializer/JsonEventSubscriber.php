<?php

/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\EventListener\Serializer;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Proxy\Proxy as ORMProxy;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Resolver\BaseUri\BaseUriResolverInterface;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class JsonEventSubscriber implements EventSubscriberInterface
{
    const EXTRA_DATA_KEY = '__DATA__';

    /**
     * Keep track of all included relationships, so that we do not duplicate them.
     *
     * @var array
     */
    protected $includedRelationships = array();

    /**
     * @var MetadataFactoryInterface
     */
    protected $jsonApiMetadataFactory;

    /**
     * @var MetadataFactoryInterface
     */
    protected $jmsMetadataFactory;

    /**
     * @var PropertyNamingStrategyInterface
     */
    protected $namingStrategy;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $currentPath;

    /**
     * @var BaseUriResolverInterface
     */
    protected $baseUriResolver;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    protected $objectHash = [];
    protected $baseUri;

    /**
     * @param MetadataFactoryInterface        $jsonApiMetadataFactory
     * @param MetadataFactoryInterface        $jmsMetadataFactory
     * @param PropertyNamingStrategyInterface $namingStrategy
     * @param RequestStack                    $requestStack
     */
    public function __construct(
        MetadataFactoryInterface $jsonApiMetadataFactory,
        MetadataFactoryInterface $jmsMetadataFactory,
        PropertyNamingStrategyInterface $namingStrategy,
        RequestStack $requestStack,
        BaseUriResolverInterface $baseUriResolver
    ) {
        $this->jsonApiMetadataFactory = $jsonApiMetadataFactory;
        $this->jmsMetadataFactory = $jmsMetadataFactory;
        $this->namingStrategy = $namingStrategy;
        $this->requestStack = $requestStack;
        $this->baseUriResolver = $baseUriResolver;

        $this->baseUri = $this->baseUriResolver->getBaseUri();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
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
        $class = $this->getClassForMetadata($object);
        $metadata = $this->getMetadata($object);

        // if it has no json api metadata, skip it
        if (null === $metadata) {
            return;
        }

        /** @var \JMS\Serializer\Metadata\ClassMetadata $jmsMetadata */
        $jmsMetadata = $this->jmsMetadataFactory->getMetadataForClass($class);

        if (!$visitor instanceof JsonApiSerializationVisitor) {
            return;
        }

        $objectProps = $this->getObjectMainProps($metadata, $object);

        $visitor->addData(
            self::EXTRA_DATA_KEY,
            [
                'id' => $objectProps['id'],
                'type' => $objectProps['type'],
            ]
        );

        $relationships = array();

        foreach ($metadata->getRelationships() as $relationship) {
            $relationshipPropertyName = $relationship->getName();

            $relationshipObject = $this->propertyAccessor->getValue($object, $relationshipPropertyName);

            // JMS Serializer support
            if (!isset($jmsMetadata->propertyMetadata[$relationshipPropertyName])) {
                continue;
            }
            $jmsPropertyMetadata = $jmsMetadata->propertyMetadata[$relationshipPropertyName];
            $relationshipPayloadKey = $this->namingStrategy->translateName($jmsPropertyMetadata);

            $relationshipData = &$relationships[$relationshipPayloadKey];
            $relationshipData = array();

            // add `links`
            $links = $this->processRelationshipLinks($objectProps, $relationship, $relationshipPayloadKey);
            if ($links) {
                $relationshipData['links'] = $links;
            }

            $include = array();
            if ($request = $this->requestStack->getCurrentRequest()) {
                $include = $request->query->get('include');
                $include = $this->parseInclude($include);
            }

            // FIXME: $includePath always is relative to the primary resource, so we can build our way with
            // class metadata to find out if we can include this relationship.
            foreach ($include as $includePath) {
                $last = end($includePath);
                if ($last === $relationship->getName()) {
                    // keep track of the path we are currently following (e.x. comments -> author)
                    $this->currentPath = $includePath;
                    $relationship->setIncludedByDefault(true);
                    // we are done here, since we have found out we can include this relationship :)
                    break;
                }
            }

            // We show the relationships data if it is included or if there are no links. We do this
            // because there MUST be links or data (see: http://jsonapi.org/format/#document-resource-object-relationships).
            if ($relationship->isIncludedByDefault() || !$links || $relationship->getShowData()) {
                // hasMany relationship
                if ($this->isIteratable($relationshipObject)) {
                    $relationshipData['data'] = array();
                    foreach ($relationshipObject as $item) {
                        $relationshipData['data'][] = $this->processRelationship($item, $relationship, $context);
                    }
                } // belongsTo relationship
                else {
                    $relationshipData['data'] = $this->processRelationship($relationshipObject, $relationship, $context);
                }
            }
        }

        if ($relationships) {
            $visitor->addData('relationships', $relationships);
        }

        // TODO: Improve link handling
        if ($metadata->getResource() && true === $metadata->getResource()->getShowLinkSelf()) {
            $visitor->addData('links', array(
                'self' => $this->baseUri.'/'.$objectProps['type'].'/'.$objectProps['id'],
            ));
        }

        $root = (array) $visitor->getRoot();
        $root['included'] = array_values($this->includedRelationships);
        $visitor->setRoot($root);
    }

    /**
     * @param Relationship $relationship
     *
     * @return array
     */
    protected function processRelationshipLinks($objectProps, Relationship $relationship, $relationshipPayloadKey)
    {
        $primaryId = $objectProps['id'];
        $type = $objectProps['type'];

        $links = array();

        // TODO: Improve this
        if ($relationship->getShowLinkSelf()) {
            $links['self'] = $this->baseUri.'/'.$type.'/'.$primaryId.'/relationships/'.$relationshipPayloadKey;
        }

        if ($relationship->getShowLinkRelated()) {
            $links['related'] = $this->baseUri.'/'.$type.'/'.$primaryId.'/'.$relationshipPayloadKey;
        }

        return $links;
    }

    /**
     * @param              $object
     * @param Relationship $relationship
     * @param Context      $context
     *
     * @return array
     */
    protected function processRelationship($object, Relationship $relationship, Context $context)
    {
        if (null === $object) {
            return null;
        }

        if (!is_object($object)) {
            throw new \RuntimeException(sprintf('Cannot process relationship "%s", because it is not an object but a %s.', $relationship->getName(), gettype($object)));
        }

        $relationshipMetadata = $this->getMetadata($object);

        if (null === $relationshipMetadata) {
            throw new \RuntimeException(sprintf(
                'Metadata for class %s not found. Did you define at as a JSON-API resource?',
                ClassUtils::getRealClass(get_class($object))
            ));
        }

        // contains the relations type and id
        $relationshipDataArray = $this->getRelationshipDataArray($relationshipMetadata, $object);

        // only include this relationship if it is needed
        if ($relationship->isIncludedByDefault() && $this->canIncludeRelationship($relationshipMetadata, $object)) {
            $includedRelationship = $relationshipDataArray; // copy data array so we do not override it with our reference

            $objectId = $includedRelationship['id'];
            $type = $includedRelationship['type'];
            $hashKey = $this->getRelationshipHashKey($type, $objectId);

            $this->includedRelationships[$hashKey] = &$includedRelationship;
            $includedRelationship = $context->accept($object); // override previous reference with the serialized data
        }

        // the relationship data can only contain one reference to another resource
        return $relationshipDataArray;
    }

    /**
     * @param $include
     *
     * @return array
     */
    protected function parseInclude($include)
    {
        $array = array();
        $parts = array_map('trim', explode(',', $include));

        foreach ($parts as $part) {
            $resources = array_map('trim', explode('.', $part));
            $array[] = $resources;
        }

        return $array;
    }

    protected function getClassForMetadata($object)
    {
        if ($object instanceof Proxy || $object instanceof ORMProxy) {
            return get_parent_class($object);
        }

        return get_class($object);
    }

    /**
     * @param $object
     * @return ClassMetadata
     */
    protected function getMetadata($object)
    {
        $class = $this->getClassForMetadata($object);

        /** @var ClassMetadata $metadata */
        $metadata = $this->jsonApiMetadataFactory->getMetadataForClass($class);

        if (null === $metadata) {
            throw new \RuntimeException(sprintf(
                'Metadata for class %s not found. Did you define at as a JSON-API resource?',
                ClassUtils::getRealClass(get_class($object))
            ));
        }

        return $metadata;
    }

    /**
     * Get the real ID of the given object by it's metadata.
     *
     * @param ClassMetadata $classMetadata
     * @param               $object
     *
     * @return mixed
     */
    protected function getId(ClassMetadata $classMetadata, $object)
    {
        return $this->propertyAccessor->getValue($object, $classMetadata->getIdField());
    }

    /**
     * @param array $resources
     * @param int   $index
     *
     * @return array
     */
    protected function parseIncludeResources(array $resources, $index = 0)
    {
        if (isset($resources[$index + 1])) {
            $resource = array_shift($resources);

            return array(
                $resource => $this->parseIncludeResources($resources),
            );
        }

        return array(
            end($resources) => 1,
        );
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param               $id
     *
     * @return array
     */
    protected function getRelationshipDataArray(ClassMetadata $classMetadata, $object)
    {
        $resource = $classMetadata->getResource();

        if (!$resource) {
            return null;
        }

        $objectProps = $this->getObjectMainProps($classMetadata, $object);

        return array(
            'type' => $objectProps['type'],
            'id' => $objectProps['id'],
        );
    }

    /**
     * Checks if an object is really empty, also if it is iteratable and has zero items.
     *
     * @param $object
     *
     * @return bool
     */
    protected function isEmpty($object)
    {
        return empty($object) || ($this->isIteratable($object) && count($object) === 0);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    protected function isIteratable($data)
    {
        return is_array($data) || $data instanceof \Traversable;
    }

    protected function getRelationshipHashKey($type, $objectId)
    {
        return $type . '_' . $objectId;
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param               $object
     *
     * @return bool
     */
    protected function canIncludeRelationship(ClassMetadata $classMetadata, $object)
    {
        $objectProps = $this->getObjectMainProps($classMetadata, $object);
        $hash = $objectProps['hash'];

        return !isset($this->includedRelationships[$hash]);
    }

    protected function getObjectMainProps(ClassMetadata $classMetadata, $object)
    {
        $hash = spl_object_hash($object);

        if (!isset($this->objectHash[$hash])) {
            $id = $this->getId($classMetadata, $object);
            $type = $classMetadata->getResource()->getType($object);

            $this->objectHash[$hash] = [
                'id' => $id,
                'type' => $type,
                'hash' => $this->getRelationshipHashKey($type, $id),
            ];
        }

        return $this->objectHash[$hash];
    }
}
