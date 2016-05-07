<?php

/*
 * This file is part of the Mango package.
 *
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
use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class ClassMetadata extends MergeableClassMetadata implements ClassMetadataInterface
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var boolean
     */
    protected $error;

    /**
     * @var string
     */
    protected $idField;

    /**
     * @var Collection|Relationship[]
     */
    protected $relationships;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->relationships = new ArrayCollection();
    }

    /**
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param Resource $resource
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->error;
    }

    /**
     * 
     */
    public function markAsError()
    {
        $this->error = true;
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

    /**
     * {@inheritdoc}
     */
    public function setRelationships(Collection $collection)
    {
        $this->relationships = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function addRelationship($relationship)
    {
        $this->relationships->add($relationship);
    }

    /**
     * {@inheritDoc}
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
            $this->error ? 1 : 0,
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
            $error,
            $this->idField,
            $this->relationships,
            $parentStr
        ) = unserialize($str);

        $this->error = 1 === $error;
        
        parent::unserialize($parentStr);
    }
}
