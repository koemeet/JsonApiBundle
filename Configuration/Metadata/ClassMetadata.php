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
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
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
}
