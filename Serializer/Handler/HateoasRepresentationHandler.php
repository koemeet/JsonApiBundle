<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation as HateoasPaginatedRepresentation;
use Mango\Bundle\JsonApiBundle\Representation\PaginatedRepresentation;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class HateoasRepresentationHandler extends AbstractPaginationHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return HateoasPaginatedRepresentation::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function createPaginatedRepresentation($paginatedRepresentation)
    {
        $items = $paginatedRepresentation->getInline();

        if ($items instanceof CollectionRepresentation) {
            $items = array_values($items->getResources());
        }

        if ($items instanceof \ArrayIterator) {
            $items = $items->getArrayCopy();
        }

        return new PaginatedRepresentation(
            $items,
            $paginatedRepresentation->getPage(),
            $paginatedRepresentation->getLimit(),
            $paginatedRepresentation->getPages(),
            $paginatedRepresentation->getTotal()
        );
    }
}
