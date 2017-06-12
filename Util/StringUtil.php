<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Util;

use Doctrine\Common\Inflector\Inflector;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class StringUtil
{
    /**
     * Dasherize string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function dasherize($string)
    {
        return Inflector::pluralize(str_replace('_', '-', Inflector::tableize($string)));
    }
}
