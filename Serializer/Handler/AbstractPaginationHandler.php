<?php
/*
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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
abstract class AbstractPaginationHandler implements SubscribingHandlerInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json:api',
                'type'      => static::getType(),
                'method'    => 'serialize',
            ],
        ];
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
    ) {
        $representation = $this->createPaginatedRepresentation($object);

        if (false === $visitor->isJsonApiDocument()) {
            return $context->accept($representation->getItems());
        }

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
    ) {
        // serialize items
        $data = $context->accept($representation->getItems());

        $root = $visitor->getRoot();

        $root['meta'] = [
            'page'  => $representation->getPage(),
            'limit' => $representation->getLimit(),
            'pages' => $representation->getPages(),
            'total' => $representation->getTotal(),
        ];

        $root['links'] = [
            'first'    => $this->getUriForPage(1, $representation->getLimit()),
            'last'     => $this->getUriForPage($representation->getPages(), $representation->getLimit()),
            'next'     => $representation->hasNextPage() ? $this->getUriForPage($representation->getNextPage(), $representation->getLimit()) : null,
            'previous' => $representation->hasPreviousPage() ? $this->getUriForPage($representation->getPreviousPage(), $representation->getLimit()) : null,
        ];

        $visitor->setRoot($root);

        return $data;
    }

    /**
     * Get uri for page
     *
     * @param int $page
     * @param int $limit
     *
     * @return string
     */
    protected function getUriForPage($page, $limit)
    {
        $request = $this->requestStack->getCurrentRequest();

        $request->query->set(
            'page',
            [
                'number' => $page,
                'size'   => $limit
            ]
        );

        $query = urldecode(http_build_query($request->query->all()));

        return $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo() . '?' . $query;
    }

    /**
     * Returns the class name of the type that needs to be transformed.
     *
     * @return string
     */
    public static function getType()
    {
        throw new \RuntimeException('The method "getType" must be implemented.');
    }

    /**
     * Create a paginated representation from the given type.
     *
     * @param mixed $object An instance of the type you are targeting
     *
     * @return PaginatedRepresentation
     */
    abstract protected function createPaginatedRepresentation($object);
}
