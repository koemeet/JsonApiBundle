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
        $visitor->getNavigator()->accept($pagerfanta->getCurrentPageResults(), null, $context);

        $root = $visitor->getRoot();
        $root['meta'] = array(
            'page' => $pagerfanta->getCurrentPage(),
            'limit' => $pagerfanta->getMaxPerPage(),
            'pages' => $pagerfanta->getNbPages(),
            'total' => $pagerfanta->getNbResults()
        );

        // TODO: Support for absolute URLs?
        $path = $this->requestStack->getCurrentRequest()->getPathInfo();

        $root['links'] = array(
            'first' => $path . '?page[number]=1',
            'last' => $path . '?page[number]=' . $pagerfanta->getNbPages(),
            'prev' => ($pagerfanta->hasPreviousPage()) ? $path . '?page[number]=' . $pagerfanta->getPreviousPage() : null,
            'next' => ($pagerfanta->hasNextPage()) ? $path . '?page[number]=' . $pagerfanta->getNextPage() : null
        );

        $visitor->setRoot($root);

        return $root;
    }
}
