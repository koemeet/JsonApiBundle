<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Util;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Json api serializer trait
 *
 * @author Sergey Chernecov <sergey.chernecov@gmail.com>
 */
trait JsonApiSerializerTrait
{
    /**
     * Serialize
     *
     * @param mixed                     $data
     * @param string|null               $format
     * @param SerializationContext|null $serializationContext
     *
     * @return string
     * @throws \Exception
     */
    public function serialize(
        $data = null,
        $format = null,
        SerializationContext $serializationContext = null
    ) {
        $format = $format ? : MangoJsonApiBundle::FORMAT;

        return $this->getSerializer()
            ->serialize(
                $data,
                $format,
                $serializationContext
            );
    }

    /**
     * Build pagerfanta
     *
     * @param AdapterInterface $adapter
     *
     * @return Pagerfanta
     */
    public function buildPagerfanta(AdapterInterface $adapter)
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        $pageFoundation = $request->get('page', []);

        $pager = new Pagerfanta($adapter);

        if (!empty($pageFoundation['size'])) {
            $pager->setMaxPerPage($pageFoundation['size']);
        }

        if (!empty($pageFoundation['number'])) {
            $pager->setCurrentPage($pageFoundation['number']);
        }

        return $pager;
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @throws \Exception
     */
    private function getSerializer()
    {
        try {
            return $this->get('json_api.serializer');
        } catch (\Exception $exception) {
            throw new \Exception(
                'Given trait assumes that class implements Psr\Container\ContainerInterface or at ' .
                'least has get method to get service from container by name'
            );
        }
    }
}
