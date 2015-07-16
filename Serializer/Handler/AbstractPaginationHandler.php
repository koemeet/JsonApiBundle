<?php
/**
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Mango\Bundle\JsonApiBundle\Representation\PaginatedRepresentation;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;

/**
 * AbstractPaginationHandler
 *
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
abstract class AbstractPaginationHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => static::getType(),
                'method' => 'serialize',
            ),
        );
    }

    /**
     * @param JsonApiSerializationVisitor $visitor
     * @param                             $object
     * @param array                       $type
     * @param Context                     $context
     *
     * @return array
     */
    public function serialize(
        JsonApiSerializationVisitor $visitor,
        $object,
        array $type,
        Context $context
    )
    {
        $representation = $this->createPaginatedRepresentation($object);
        return $this->transformRoot($representation, $visitor, $context);
    }

    /**
     * Transforms root of visitor with additional data based on the representation.
     *
     * @param PaginatedRepresentation     $representation
     * @param JsonApiSerializationVisitor $visitor
     * @param Context                     $context
     *
     * @return mixed
     */
    protected function transformRoot(
        PaginatedRepresentation $representation,
        JsonApiSerializationVisitor $visitor,
        Context $context
    )
    {
        // serialize items
        $data = $context->accept($representation->getItems());

        $root = $visitor->getRoot();

        $root['meta'] = array(
            'page' => $representation->getPage(),
            'limit' => $representation->getLimit(),
            'pages' => $representation->getPages(),
            'total' => $representation->getTotal()
        );

        $visitor->setRoot($root);

        return $data;
    }

    /**
     * @param $page
     *
     * @return string
     */
    protected function getUriForPage($page)
    {
        $request = clone $this->requestStack->getCurrentRequest();

        $queryPage = $request->query->get('page');
        $queryPage['number'] = $page;
        $request->query->set('page', $queryPage);

        $query = urldecode(http_build_query($request->query->all()));

        return $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo() . '?' . $query;
    }

    /**
     * Returns the class name of the type that needs to be transformed.
     *
     * @return string
     */
    abstract public static function getType();

    /**
     * Create a paginated representation from the given type.
     *
     * @param mixed $object An instance of the type you are targeting
     *
     * @return PaginatedRepresentation
     */
    abstract protected function createPaginatedRepresentation($object);
}
