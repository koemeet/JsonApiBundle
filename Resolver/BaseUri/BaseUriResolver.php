<?php

/*
 * (c) Steffen Brem
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Resolver\BaseUri;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class BaseUriResolver implements BaseUriResolverInterface
{
    /**
     * @var string
     */
    private $baseUri;

    /**
     * @param string $baseUri
     */
    public function __construct($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @inheritDoc
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }
}
