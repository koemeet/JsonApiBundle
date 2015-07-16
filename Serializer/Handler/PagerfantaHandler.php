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
use Mango\Bundle\JsonApiBundle\Representation\PaginatedRepresentation;
use Pagerfanta\Pagerfanta;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class PagerfantaHandler extends AbstractPaginationHandler
{
    /**
     * @return string
     */
    public static function getType()
    {
        return 'Pagerfanta\Pagerfanta';
    }

    /**
     * @param Pagerfanta $object
     * @return PaginatedRepresentation
     */
    protected function createPaginatedRepresentation($object)
    {
        if (!$object instanceof Pagerfanta) {
            return;
        }

        $items = $object->getCurrentPageResults();

        return new PaginatedRepresentation(
            $items,
            $object->getCurrentPage(),
            $object->getMaxPerPage(),
            $object->getNbPages(),
            $object->getNbResults()
        );
    }
}