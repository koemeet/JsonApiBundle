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
class Route
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|array
     */
    protected $parameters;

    /**
     * @var boolean
     */
    protected $absolute;

    /**
     * @var null|string
     */
    protected $generator;

    /**
     * @param string       $name
     * @param string|array $parameters
     * @param boolean      $absolute
     * @param string|null  $generator
     */
    public function __construct($name, $parameters = [], $absolute = false, $generator = null)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->absolute = $absolute;
        $this->generator = $generator;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return boolean
     */
    public function isAbsolute()
    {
        return $this->absolute;
    }

    /**
     * @return null|string
     */
    public function getGenerator()
    {
        return $this->generator;
    }
}
