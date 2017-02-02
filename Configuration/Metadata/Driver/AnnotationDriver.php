<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\Inflector;
use Mango\Bundle\JsonApiBundle\Configuration\Annotation;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;
use Mango\Bundle\JsonApiBundle\Util\StringUtil;
use Metadata\Driver\DriverInterface;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $annotations = $this->reader->getClassAnnotations($class);

        if (0 === count($annotations)) {
            return null;
        }

        $classMetadata = new ClassMetadata($class->getName());
        $classMetadata->fileResources[] = $class->getFileName();

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Resource) {
                $classMetadata->setResource(new Resource($annotation->type, $annotation->showLinkSelf));
            }
        }

        $classProperties = $class->getProperties();

        foreach ($classProperties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);

            foreach ($annotations as $annotation) {
                if ($annotation instanceof Annotation\Id) {
                    $classMetadata->setIdField($property->getName());
                } else if ($annotation instanceof Annotation\Relationship) {
                    $classMetadata->addRelationship(new Relationship(
                        $property->getName(),
                        $annotation->includeByDefault,
                        $annotation->showData,
                        $annotation->showLinkSelf,
                        $annotation->showLinkRelated
                    ));
                }
            }
        }

        return $classMetadata;
    }
}
