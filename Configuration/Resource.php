<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration;

use Mango\Bundle\JsonApiBundle\Util\StringUtil;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class Resource
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $route;
    
    /**
     * @var bool
     */
    private $showLinkSelf = true;

    /**
     * @param $type
     * @param $route
     * @param $showLinkSelf
     */
    public function __construct($type, $route = null, $showLinkSelf = null)
    {
        if (null === $type) {
            throw new \RuntimeException('A JSON-API resource must have a type defined and cannot be "null".');
        }

        $this->type = $type;

        if (null !== $route) {
            $this->route = $route;
        } else {
            $this->route = StringUtil::resourceNameToResourceRoute($type);
        }

        if (null !== $showLinkSelf) {
            $this->showLinkSelf = $showLinkSelf;
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }
    
    /**
     * @return boolean
     */
    public function getShowLinkSelf()
    {
        return (bool)$this->showLinkSelf;
    }
}
