<?php
/**
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer;

use Mango\Bundle\JsonApiBundle\Representation\PaginatedRepresentation;

/**
 * PagintedRepresentationTransformer
 *
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class PaginatedRepresentationTransformer
{
    /**
     * @param PaginatedRepresentation $representation
     * @return array
     */
    public function transform(PaginatedRepresentation $representation)
    {
        $data = array();

        $data['meta'] = array(
            'page' => $representation->getPage(),
            'limit' => $representation->getLimit(),
            'pages' => $representation->getPages(),
            'total' => $representation->getTotal()
        );

        return $data;
    }
}
