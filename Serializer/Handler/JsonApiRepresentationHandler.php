<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use Mango\Bundle\JsonApiBundle\Representation\OffsetPaginatedRepresentation;

/**
 * 
 */
class JsonApiRepresentationHandler extends AbstractPaginationHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return OffsetPaginatedRepresentation::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function createPaginatedRepresentation($paginatedRepresentation)
    {
        return $paginatedRepresentation;
    }
}
