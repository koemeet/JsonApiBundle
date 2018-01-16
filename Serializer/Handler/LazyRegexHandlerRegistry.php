<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Lazy regex handler registry
 *
 * @author Sergey Chernecov <sergey.chernecov@gmail.com>
 */
class LazyRegexHandlerRegistry extends HandlerRegistry
{
    /**
     * Container
     *
     * @var PsrContainerInterface|ContainerInterface
     */
    private $container;

    /**
     * Initialized handlers
     *
     * @var array|SubscribingHandlerInterface[]
     */
    private $initializedHandlers = array();

    /**
     * Lazy regex handler registry constructor
     *
     * @param PsrContainerInterface|ContainerInterface $container
     * @param array|SubscribingHandlerInterface[]      $handlers
     */
    public function __construct($container, array $handlers = array())
    {
        if (!$container instanceof PsrContainerInterface && !$container instanceof ContainerInterface) {
            throw new \InvalidArgumentException(sprintf('The container must be an instance of %s or %s (%s given).', PsrContainerInterface::class, ContainerInterface::class, is_object($container) ? get_class($container) : gettype($container)));
        }

        parent::__construct($handlers);
        $this->container = $container;
    }

    /**
     * Get handler
     *
     * @param int    $direction
     * @param string $typeName
     * @param string $format
     *
     * @return array|null
     */
    public function getHandler($direction, $typeName, $format)
    {
        if (isset($this->initializedHandlers[$direction][$typeName][$format])) {
            return $this->initializedHandlers[$direction][$typeName][$format];
        }

        $handler = $this->getExactHandler($direction, $typeName, $format);

        if ($handler) {
            return $this->initializeHandler($direction, $typeName, $format, $handler);
        }

        foreach ($this->handlers[$direction] as $type => $handler) {
            if (is_subclass_of($typeName, $type)) {
                if (!empty($handler[$format])) {
                    return $this->initializeHandler($direction, $type, $format, $handler[$format]);
                }
            }
        }

        return null;
    }

    /**
     * Get exact handler
     *
     * @param int    $direction
     * @param string $typeName
     * @param string $format
     *
     * @return array|null
     */
    private function getExactHandler($direction, $typeName, $format)
    {
        if (empty($this->handlers[$direction][$typeName][$format])) {
            return null;
        }

        return $this->handlers[$direction][$typeName][$format];
    }

    /**
     * Initialize handler
     *
     * @param int    $direction
     * @param string $typeName
     * @param string $format
     * @param array  $handler
     *
     * @return array
     */
    private function initializeHandler($direction, $typeName, $format, $handler)
    {
        if (is_array($handler) && is_string($handler[0]) && $this->container->has($handler[0])) {
            $handler[0] = $this->container->get($handler[0]);
        }

        $this->initializedHandlers[$direction][$typeName][$format] = $handler;
        
        return $handler;
    }
}
