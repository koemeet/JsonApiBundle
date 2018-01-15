<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata;

use Doctrine\Common\Collections\Collection;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
interface ClassMetadataInterface
{
    /**
     * @return resource
     */
    public function getResource();

    /**
     * @param resource $resource
     */
    public function setResource(Resource $resource);

    /**
     * @return string
     */
    public function getIdField();

    /**
     * @param string $idField
     */
    public function setIdField($idField);

    /**
     * @return Collection|Relationship[]
     */
    public function getRelationships();

    /**
     * @param Collection $collection
     */
    public function setRelationships(Collection $collection);

    /**
     * @param Relationship $relationship
     */
    public function addRelationship($relationship);
}
