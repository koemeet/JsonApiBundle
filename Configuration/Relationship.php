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
class Relationship
{
    protected $name;

    protected $includedByDefault = false;

    /**
     * @param $includedByDefault
     */
    public function __construct($name, $includedByDefault)
    {
        $this->name = $name;
        $this->includedByDefault = $includedByDefault;
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
}
