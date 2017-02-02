<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class SerializerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('jms_serializer.json_serialization_visitor')
            ->addArgument($container->getDefinition('json_api.metadata_factory'))
            ->addArgument('%json_api.show_version_info%')
            ->setClass('Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor')
        ;
    }
}
