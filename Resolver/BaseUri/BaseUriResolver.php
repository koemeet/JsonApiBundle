<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Resolver\BaseUri;

use Symfony\Component\HttpFoundation\RequestStack;

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
     * Request stack
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Base uri resolver constructor
     *
     * @param RequestStack $requestStack
     * @param string       $baseUri
     */
    public function __construct(RequestStack $requestStack, $baseUri)
    {
        $this->requestStack = $requestStack;
        $this->baseUri = $baseUri;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri($isAbsolute)
    {
        if (!$isAbsolute) {
            return $this->baseUri;
        }

        return $this->requestStack->getCurrentRequest()->getUriForPath($this->baseUri);
    }
}
