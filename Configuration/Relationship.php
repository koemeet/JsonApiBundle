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
class Relationship
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $includedByDefault = false;

    /**
     * @var bool
     */
    protected $showData = false;

    /**
     * @var bool
     */
    protected $showLinkSelf = false;

    /**
     * @var bool
     */
    protected $showLinkRelated = false;

    /**
     * @var string
     */
    protected $route;

    /**
     * @param             $name
     * @param bool|false  $includedByDefault
     * @param bool|false  $showData
     * @param bool|false  $showLinkSelf
     * @param bool|false  $showLinkRelated
     * @param string|null $route
     */
    public function __construct($name, $includedByDefault = null, $showData = null, $showLinkSelf = null, $showLinkRelated = null, $route = null)
    {
        $this->name = $name;

        if (null !== $includedByDefault) {
            $this->includedByDefault = $includedByDefault;
        }

        if (null !== $showData) {
            $this->showData = $showData;
        }

        if (null !== $showLinkSelf) {
            $this->showLinkSelf = $showLinkSelf;
        }

        if (null !== $showLinkRelated) {
            $this->showLinkRelated = $showLinkRelated;
        }

        if (null !== $route) {
            $this->route = $route;
        } else {
            $this->route = StringUtil::resourceNameToResourceRoute($name);
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isIncludedByDefault()
    {
        return $this->includedByDefault;
    }

    /**
     * @param $bool
     */
    public function setIncludedByDefault($bool)
    {
        $this->includedByDefault = $bool;
    }

    /**
     * @return boolean
     */
    public function getShowData()
    {
        return $this->showData;
    }

    /**
     * @param boolean $showData
     */
    public function setShowData($showData)
    {
        $this->showData = $showData;
    }

    /**
     * @return boolean
     */
    public function getShowLinkSelf()
    {
        return $this->showLinkSelf;
    }

    /**
     * @return boolean
     */
    public function getShowLinkRelated()
    {
        return $this->showLinkRelated;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

}
