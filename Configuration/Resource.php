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
    private $showLinkSelf;

    /**
     * @param string $type
     */
    public function __construct($type, $showLinkSelf)
    {
        $this->type = $type;
        $this->showLinkSelf = $showLinkSelf;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function getShowLinkSelf()
    {
        return $this->showLinkSelf;
    }
}
