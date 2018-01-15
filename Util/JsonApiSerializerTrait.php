<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Util;

use JMS\Serializer\SerializationContext;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $format = $format ?: MangoJsonApiBundle::FORMAT;

        switch (true) {
            case $this instanceof Controller:
            case $this instanceof ContainerInterface:
                return $this->get('json_api.serializer')->serialize($data, $format, $serializationContext);
                break;
            default:
                throw new \Exception(
                    sprintf(
                        'Given trait assumes that class implements at least one of: "%s"',
                        implode(
                            '","',
                            [
                                ContainerInterface::class,
                                Controller::class
                            ]
                        )
                    )
                );
                break;
        }
    }
}
