<?php

namespace Mango\Bundle\JsonApiBundle;

use Mango\Bundle\JsonApiBundle\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class MangoJsonApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SerializerPass());
    }
}
