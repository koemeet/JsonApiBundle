<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;
use Metadata\MergeableInterface;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class ClassMetadata extends \JMS\Serializer\Metadata\ClassMetadata implements ClassMetadataInterface
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected $idField;

    /**
     * @var Collection|Relationship[]
     */
    protected $relationships;

    /**
     * @var array
     */
    protected $relationshipsHash;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->relationships = new ArrayCollection();
        $this->relationshipsHash = [];
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param resource $resource
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getIdField()
    {
        if (null === $this->idField) {
            return 'id';
        }

        return $this->idField;
    }

    /**
     * @param string $idField
     */
    public function setIdField($idField)
    {
        $this->idField = $idField;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    public function getRelationshipsHash()
    {
        return $this->relationshipsHash;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelationships(Collection $collection)
    {
        $this->relationships = $collection;

        foreach ($collection as $relationship) {
            $this->relationshipsHash[$relationship->getName()] = $relationship;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRelationship($relationship)
    {
        $this->relationships->add($relationship);
        $this->relationshipsHash[$relationship->getName()] = $relationship;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableInterface $object)
    {
        if (!$object instanceof self) {
            throw new \InvalidArgumentException(sprintf('Object must be an instance of %s.', __CLASS__));
        }

        parent::merge($object);

        $this->resource = $object->getResource();
        $this->idField = $object->getIdField();

        foreach ($object->getRelationships() as $relationship) {
            $this->addRelationship($relationship);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->resource,
            $this->idField,
            $this->relationships,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->resource,
            $this->idField,
            $this->relationships,
            $parentStr
            ) = unserialize($str);

        foreach ($this->relationships as $relationship) {
            $this->relationshipsHash[$relationship->getName()] = $relationship;
        }

        parent::unserialize($parentStr);
    }
}
