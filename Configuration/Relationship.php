<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration;

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
     * Absolute urls
     *
     * @var bool
     */
    protected $absolute = false;

    /**
     * @var bool
     */
    protected $showLinkRelated = false;

    /**
     * @param            $name
     * @param bool|false $includedByDefault
     * @param bool|false $showData
     * @param bool|false $showLinkSelf
     * @param bool|false $showLinkRelated
     * @param bool|false $absolute
     */
    public function __construct(
        $name,
        $includedByDefault = null,
        $showData = null,
        $showLinkSelf = null,
        $showLinkRelated = null,
        $absolute = null
    ) {
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

        if (null !== $absolute) {
            $this->absolute = $absolute;
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
     * @return bool
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
     * @return bool
     */
    public function getShowData()
    {
        return $this->showData;
    }

    /**
     * @param bool $showData
     */
    public function setShowData($showData)
    {
        $this->showData = $showData;
    }

    /**
     * @return bool
     */
    public function getShowLinkSelf()
    {
        return $this->showLinkSelf;
    }

    /**
     * @return bool
     */
    public function getShowLinkRelated()
    {
        return $this->showLinkRelated;
    }

    /**
     * @return bool
     */
    public function isAbsolute()
    {
        return $this->absolute;
    }
}
