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

use Metadata\ClassHierarchyMetadata;
use Metadata\MergeableClassMetadata;
use Metadata\MetadataFactory as BaseMetadataFactory;

/**
 * 
 */
class MetadataFactory extends BaseMetadataFactory implements JsonApiResourceMetadataFactoryInterface
{
    protected $resourceToClassMapping = [];
    
    public function setResourceToClassMapping(array $resourceToClassMapping)
    {
        $this->resourceToClassMapping = $resourceToClassMapping;
    }

    /**
     * @param string $resourceName
     * @return ClassHierarchyMetadata|MergeableClassMetadata|null
     */
    public function getMetadataForResource($resourceName)
    {
        if (isset($this->resourceToClassMapping[$resourceName])) {
            return $this->getMetadataForClass($this->resourceToClassMapping[$resourceName]);
        }

        return null;
    }
}
