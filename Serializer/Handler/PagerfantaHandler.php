<?php
/*
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
        return Pagerfanta::class;
    }

    /**
     * @param mixed $object
     *
     * @return PaginatedRepresentation
     * @throws \Exception
     */
    protected function createPaginatedRepresentation($object)
    {
        if (!$object instanceof Pagerfanta) {
            throw new \Exception(
                sprintf(
                    'Wrong parameter given. Expected instance of "%s"',
                    'Pagerfanta\Pagerfanta'
                )
            );
        }

        $items = $object->getCurrentPageResults();

        if ($items instanceof \ArrayIterator) {
            $items = $items->getArrayCopy();
        }

        return new PaginatedRepresentation(
            $items,
            $object->getCurrentPage(),
            $object->getMaxPerPage(),
            $object->getNbPages(),
            $object->getNbResults()
        );
    }
}
