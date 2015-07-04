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
use Doctrine\Common\Collections\Collection;

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
    protected $includedByDefault;

    /**
     * @var bool
     */
    protected $showLinkSelf;

    /**
     * @var bool
     */
    protected $showLinkRelated;

    /**
     * @param            $name
     * @param bool|false $includedByDefault
     * @param bool|false $showLinkSelf
     * @param bool|false $showLinkRelated
     */
    public function __construct($name, $includedByDefault = false, $showLinkSelf = false, $showLinkRelated = false)
    {
        $this->name = $name;
        $this->includedByDefault = $includedByDefault;
        $this->showLinkSelf = $showLinkSelf;
        $this->showLinkRelated = $showLinkRelated;
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
}
