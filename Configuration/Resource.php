<?php
/*
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
     * @var bool
     */
    protected $absolute = false;

    /**
     * Resource constructor
     *
     * @param string    $type
     * @param bool|null $showLinkSelf
     * @param bool|     $absolute
     */
    public function __construct($type, $showLinkSelf = null, $absolute = null)
    {
        $this->type = $type;

        if (null !== $showLinkSelf) {
            $this->showLinkSelf = $showLinkSelf;
        }

        if (null !== $absolute) {
            $this->absolute = $absolute;
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
     * @return bool
     */
    public function getShowLinkSelf()
    {
        return (bool) $this->showLinkSelf;
    }

    /**
     * @return bool
     */
    public function isAbsolute()
    {
        return $this->absolute;
    }
}
