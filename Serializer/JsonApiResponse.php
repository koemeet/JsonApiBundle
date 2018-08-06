<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Serializer;

use Symfony\Component\HttpFoundation\Response;

/**
 * Response represents an HTTP response in JSON:API format
 *
 * @author Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
class JsonApiResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function __construct($content = '', $status = 200, $headers = [])
    {
        parent::__construct($content, $status, $headers);

        // Only set the header when there is none in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'application/vnd.api+json');
        }
    }
}