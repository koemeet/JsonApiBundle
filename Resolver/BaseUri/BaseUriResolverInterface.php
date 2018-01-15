<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Resolver\BaseUri;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
interface BaseUriResolverInterface
{
    /**
     * @param bool $isAbsolute
     *
     * @return string
     */
    public function getBaseUri($isAbsolute);
}
