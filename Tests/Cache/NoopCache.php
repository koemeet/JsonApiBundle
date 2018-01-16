<?php

namespace Mango\Bundle\JsonApiBundle\Tests\Cache;

use Metadata\Cache\CacheInterface;
use Metadata\ClassMetadata;

class NoopCache implements CacheInterface
{
    /**
     * {@inheritDoc}
     */
    public function loadClassMetadataFromCache(\ReflectionClass $class)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function putClassMetadataInCache(ClassMetadata $metadata)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function evictClassMetadataFromCache(\ReflectionClass $class)
    {
        return null;
    }
}
