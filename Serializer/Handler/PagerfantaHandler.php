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

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class PagerfantaHandler implements SubscribingHandlerInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var array
     */
    protected $paginationOptions;

    /**
     * @param RequestStack $requestStack
     * @param array        $paginationOptions
     */
    public function __construct(RequestStack $requestStack, array $paginationOptions)
    {
        $this->requestStack = $requestStack;
        $this->paginationOptions = $paginationOptions;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Pagerfanta\Pagerfanta',
                'method' => 'serializePagerfanta',
            ),
        );
    }

    /**
     * @param JsonApiSerializationVisitor $visitor
     * @param Pagerfanta                  $pagerfanta
     * @param array                       $type
     * @param Context                     $context
     * @return Pagerfanta
     */
    public function serializePagerfanta(JsonApiSerializationVisitor $visitor, Pagerfanta $pagerfanta, array $type, Context $context)
    {
        $request = $this->requestStack->getCurrentRequest();

        $pagerfanta->setNormalizeOutOfRangePages(true);
        $pagerfanta->setAllowOutOfRangePages(true);

        $pagerfanta->setMaxPerPage($request->get('page[limit]', $this->paginationOptions['limit'], true));
        $pagerfanta->setCurrentPage($request->get('page[number]', 1, true));

        $results = $pagerfanta->getCurrentPageResults();

        if ($results instanceof \ArrayIterator) {
            $results = $results->getArrayCopy();
        }

        $data = $context->accept($results);

        $root = $visitor->getRoot();
        $root['meta'] = array(
            'page' => $pagerfanta->getCurrentPage(),
            'limit' => $pagerfanta->getMaxPerPage(),
            'pages' => $pagerfanta->getNbPages(),
            'total' => $pagerfanta->getNbResults()
        );

        $root['links'] = array(
            'first' => $this->getUriForPage(1),
            'last' => $this->getUriForPage($pagerfanta->getNbPages()),
            'prev' => ($pagerfanta->hasPreviousPage()) ? $this->getUriForPage($pagerfanta->getPreviousPage()) : null,
            'next' => ($pagerfanta->hasNextPage()) ? $this->getUriForPage($pagerfanta->getNextPage()) : null
        );

        $visitor->setRoot($root);

        return $data;
    }

    /**
     * @param $page
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
}
