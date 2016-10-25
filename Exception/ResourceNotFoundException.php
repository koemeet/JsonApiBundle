<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ResourceNotFoundException
 */
class ResourceNotFoundException extends NotFoundHttpException
{
    protected $resourceType;
    
    protected $resourceId;
    
    public function __construct($resourceId, $resourceType, \Exception $previous = null, $code = 0)
    {
        $this->resourceId = $resourceId;
        $this->resourceType = $resourceType;

        parent::__construct(sprintf("Resource %s#%s not found ", $resourceType, $resourceId), $previous, $code);
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @return integer
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }
}
