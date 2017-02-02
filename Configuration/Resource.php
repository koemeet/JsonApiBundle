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
     * @var bool
     */
    private $showLinkSelf = true;

    /**
     * @param $type
     * @param $showLinkSelf
     */
    public function __construct($type, $showLinkSelf = null)
    {
        $this->type = $type;

        if (null !== $showLinkSelf) {
            $this->showLinkSelf = $showLinkSelf;
        }
    }

    /**
     * @param null|object $object
     *
     * @return string
     */
    public function getType($object)
    {
        if (!$this->type) {
            $reflectionClass = new \ReflectionClass($object);
            return StringUtil::dasherize($reflectionClass->getShortName());
        }

        return $this->type;
    }

    /**
     * @return boolean
     */
    public function getShowLinkSelf()
    {
        return (bool)$this->showLinkSelf;
    }
}
