<?php

/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
final class Relationship
{
    /**
     * @var bool
     */
    public $includeByDefault;

    /**
     * @var bool
     */
    public $showData;

    /**
     * @var bool
     */
    public $showLinkSelf;

    /**
     * @var bool
     */
    public $showLinkRelated;
}
