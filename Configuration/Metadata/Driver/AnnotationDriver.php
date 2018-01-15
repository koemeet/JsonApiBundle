<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\AccessorOrder;
use JMS\Serializer\Annotation\AccessType;
use JMS\Serializer\Annotation\Discriminator;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\HandlerCallback;
use JMS\Serializer\Annotation\Inline;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\PostDeserialize;
use JMS\Serializer\Annotation\PostSerialize;
use JMS\Serializer\Annotation\PreSerialize;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Since;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Until;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlAttributeMap;
use JMS\Serializer\Annotation\XmlDiscriminator;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlKeyValuePairs;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlMap;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\XmlValue;
use JMS\Serializer\Exception\InvalidArgumentException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\ExpressionPropertyMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Annotation;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;
use Metadata\Driver\DriverInterface;
use Metadata\MethodMetadata;

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

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new ClassMetadata($name = $class->name);
        $classMetadata->fileResources[] = $class->getFilename();

        $propertiesMetadata = array();
        $propertiesAnnotations = array();

        $exclusionPolicy = 'NONE';
        $excludeAll = false;
        $classAccessType = PropertyMetadata::ACCESS_TYPE_PROPERTY;
        $readOnlyClass = false;
        foreach ($this->reader->getClassAnnotations($class) as $annot) {
            if ($annot instanceof ExclusionPolicy) {
                $exclusionPolicy = $annot->policy;
            } elseif ($annot instanceof XmlRoot) {
                $classMetadata->xmlRootName = $annot->name;
                $classMetadata->xmlRootNamespace = $annot->namespace;
            } elseif ($annot instanceof XmlNamespace) {
                $classMetadata->registerNamespace($annot->uri, $annot->prefix);
            } elseif ($annot instanceof Exclude) {
                $excludeAll = true;
            } elseif ($annot instanceof AccessType) {
                $classAccessType = $annot->type;
            } elseif ($annot instanceof ReadOnly) {
                $readOnlyClass = true;
            } elseif ($annot instanceof AccessorOrder) {
                $classMetadata->setAccessorOrder($annot->order, $annot->custom);
            } elseif ($annot instanceof Discriminator) {
                if ($annot->disabled) {
                    $classMetadata->discriminatorDisabled = true;
                } else {
                    $classMetadata->setDiscriminator($annot->field, $annot->map, $annot->groups);
                }
            } elseif ($annot instanceof XmlDiscriminator) {
                $classMetadata->xmlDiscriminatorAttribute = (bool) $annot->attribute;
                $classMetadata->xmlDiscriminatorCData = (bool) $annot->cdata;
                $classMetadata->xmlDiscriminatorNamespace = $annot->namespace ? (string) $annot->namespace : null;
            } elseif ($annot instanceof VirtualProperty) {
                $virtualPropertyMetadata = new ExpressionPropertyMetadata($name, $annot->name, $annot->exp);
                $propertiesMetadata[] = $virtualPropertyMetadata;
                $propertiesAnnotations[] = $annot->options;
            } elseif ($annot instanceof Annotation\Resource) {
                $classMetadata->setResource(
                    new Resource(
                        $annot->type,
                        $annot->showLinkSelf,
                        $annot->absolute
                    )
                );
            }
        }

        foreach ($class->getMethods() as $method) {
            if ($method->class !== $name) {
                continue;
            }

        $methodAnnotations = $this->reader->getMethodAnnotations($method);foreach ($methodAnnotations as $annot) {
            if ($annot instanceof PreSerialize) {
                $classMetadata->addPreSerializeMethod(new MethodMetadata($name, $method->name));
                    continue 2;
                } elseif ($annot instanceof PostDeserialize) {
                    $classMetadata->addPostDeserializeMethod(new MethodMetadata($name, $method->name));
                    continue 2;
                } elseif ($annot instanceof PostSerialize) {
                    $classMetadata->addPostSerializeMethod(new MethodMetadata($name, $method->name));
                    continue 2;
                } elseif ($annot instanceof VirtualProperty) {
                    $virtualPropertyMetadata = new VirtualPropertyMetadata($name, $method->name);
                    $propertiesMetadata[] = $virtualPropertyMetadata;
                    $propertiesAnnotations[] = $methodAnnotations;
                    continue 2;
                } elseif ($annot instanceof HandlerCallback) {
                    $classMetadata->addHandlerCallback(GraphNavigator::parseDirection($annot->direction), $annot->format, $method->name);
                    continue 2;
                }
            }
        }

        if (!$excludeAll) {
            foreach ($class->getProperties() as $property) {
                if ($property->class !== $name || (isset($property->info) && $property->info['class'] !== $name)) {
                    continue;
                }
                $propertiesMetadata[] = new PropertyMetadata($name, $property->getName());
                $propertiesAnnotations[] = $this->reader->getPropertyAnnotations($property);
            }

            foreach ($propertiesMetadata as $propertyKey => $propertyMetadata) {
                $isExclude = false;
                $isExpose = $propertyMetadata instanceof VirtualPropertyMetadata
          || $propertyMetadata instanceof ExpressionPropertyMetadata;
                $propertyMetadata->readOnly = $propertyMetadata->readOnly || $readOnlyClass;
                $accessType = $classAccessType;
                $accessor = array(null, null);

                $propertyAnnotations = $propertiesAnnotations[$propertyKey];

                foreach ($propertyAnnotations as $annot) {
                    if ($annot instanceof Annotation\Id) {
                        $classMetadata->setIdField($propertyMetadata->name);
                    } elseif ($annot instanceof Annotation\Relationship) {
                        $propertyMetadata->relationship = true;
                        $classMetadata->addRelationship(new Relationship(
              $propertyMetadata->name,
              $annot->includeByDefault,
              $annot->showData,
              $annot->showLinkSelf,
              $annot->showLinkRelated,
              $annot->absolute
            ));
                    } elseif ($annot instanceof Since) {
                        $propertyMetadata->sinceVersion = $annot->version;
                    } elseif ($annot instanceof Until) {
                        $propertyMetadata->untilVersion = $annot->version;
                    } elseif ($annot instanceof SerializedName) {
                        $propertyMetadata->serializedName = $annot->name;
                    } elseif ($annot instanceof Expose) {
                        $isExpose = true;
                        if (null !== $annot->if) {
                            $propertyMetadata->excludeIf = '!('.$annot->if.')';
                        }
                    } elseif ($annot instanceof Exclude) {
                        if (null !== $annot->if) {
                            $propertyMetadata->excludeIf = $annot->if;
                        } else {
                            $isExclude = true;
                        }
                    } elseif ($annot instanceof Type) {
                        $propertyMetadata->setType($annot->name);
                    } elseif ($annot instanceof XmlElement) {
                        $propertyMetadata->xmlAttribute = false;
                        $propertyMetadata->xmlElementCData = $annot->cdata;
                        $propertyMetadata->xmlNamespace = $annot->namespace;
                    } elseif ($annot instanceof XmlList) {
                        $propertyMetadata->xmlCollection = true;
                        $propertyMetadata->xmlCollectionInline = $annot->inline;
                        $propertyMetadata->xmlEntryName = $annot->entry;
                        $propertyMetadata->xmlEntryNamespace = $annot->namespace;
                        $propertyMetadata->xmlCollectionSkipWhenEmpty = $annot->skipWhenEmpty;
                    } elseif ($annot instanceof XmlMap) {
                        $propertyMetadata->xmlCollection = true;
                        $propertyMetadata->xmlCollectionInline = $annot->inline;
                        $propertyMetadata->xmlEntryName = $annot->entry;
                        $propertyMetadata->xmlEntryNamespace = $annot->namespace;
                        $propertyMetadata->xmlKeyAttribute = $annot->keyAttribute;
                    } elseif ($annot instanceof XmlKeyValuePairs) {
                        $propertyMetadata->xmlKeyValuePairs = true;
                    } elseif ($annot instanceof XmlAttribute) {
                        $propertyMetadata->xmlAttribute = true;
                        $propertyMetadata->xmlNamespace = $annot->namespace;
                    } elseif ($annot instanceof XmlValue) {
                        $propertyMetadata->xmlValue = true;
                        $propertyMetadata->xmlElementCData = $annot->cdata;
                    } elseif ($annot instanceof XmlElement) {
                        $propertyMetadata->xmlElementCData = $annot->cdata;
                    } elseif ($annot instanceof AccessType) {
                        $accessType = $annot->type;
                    } elseif ($annot instanceof ReadOnly) {
                        $propertyMetadata->readOnly = $annot->readOnly;
                    } elseif ($annot instanceof Accessor) {
                        $accessor = array($annot->getter, $annot->setter);
                    } elseif ($annot instanceof Groups) {
                        $propertyMetadata->groups = $annot->groups;
                        foreach ((array) $propertyMetadata->groups as $groupName) {
                            if (false !== strpos($groupName, ',')) {
                                throw new InvalidArgumentException(sprintf(
                  'Invalid group name "%s" on "%s", did you mean to create multiple groups?',
                  implode(', ', $propertyMetadata->groups),
                  $propertyMetadata->class.'->'.$propertyMetadata->name
                ));
                            }
                        }
                    } elseif ($annot instanceof Inline) {
                        $propertyMetadata->inline = true;
                    } elseif ($annot instanceof XmlAttributeMap) {
                        $propertyMetadata->xmlAttributeMap = true;
                    } elseif ($annot instanceof MaxDepth) {
                        $propertyMetadata->maxDepth = $annot->depth;
                    }
                }

                if ((ExclusionPolicy::NONE === $exclusionPolicy && !$isExclude)
          || (ExclusionPolicy::ALL === $exclusionPolicy && $isExpose)
        ) {
                    $propertyMetadata->setAccessor($accessType, $accessor[0], $accessor[1]);
                    $classMetadata->addPropertyMetadata($propertyMetadata);
                }
            }
        }

        return $classMetadata;
    }
}
