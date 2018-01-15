<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Exclusion;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\Common\Proxy\Proxy as ORMProxy;
use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
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
        //$jsonApiMetadata = $this->metadataFactory->getMetadataForClass($metadata->name);

        //if (null === $jsonApiMetadata) {
        //    throw new \RuntimeException(sprintf(
        //        'Trying to serialize class %s, but it is not defined as a JSON-API resource. Either exclude it with the JMS Exclude mapping or map it as a Resource.',
        //        $metadata->name
        //    ));
        //}

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        if (!$context instanceof SerializationContext) {
            return false;
        }

        $object = $context->getObject();

        if ($object instanceof Proxy || $object instanceof ORMProxy) {
            $class = get_parent_class($object);
        } else {
            $class = get_class($object);
        }

        /** @var \Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata $metadata */
        $metadata = $this->metadataFactory->getMetadataForClass($class);

        if (!$metadata) {
            return false;
        }

        $relationshipHash = $metadata->getRelationshipsHash();

        return isset($relationshipHash[$property->name]);
    }
}
