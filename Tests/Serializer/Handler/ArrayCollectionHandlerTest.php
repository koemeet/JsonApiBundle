<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Serializer\Handler;

use JMS\Serializer\GraphNavigator;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Mango\Bundle\JsonApiBundle\Serializer\Handler\ArrayCollectionHandler;
use Mango\Bundle\JsonApiBundle\Tests\TestCase;

/**
 * ArrayCollectionHandler test
 *
 * @author Alexander Kurbatsky <alexander.kurbatsky@intexsys.lv>
 */
class ArrayCollectionHandlerTest extends TestCase
{
    /**
     * Test getSubscribingMethods method
     *
     * @return void
     */
    public function testGetSubscribingMethods()
    {
        $this->assertEquals($this->getExpectedSubscribingMethods(), ArrayCollectionHandler::getSubscribingMethods());
    }

    /**
     * Returns expected subscribing methods
     *
     * @return array
     */
    private function getExpectedSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'ArrayCollection',
                'format'    => 'json',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'ArrayCollection',
                'format'    => 'json',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'ArrayCollection',
                'format'    => 'xml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'ArrayCollection',
                'format'    => 'xml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'ArrayCollection',
                'format'    => 'yml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'ArrayCollection',
                'format'    => 'yml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\Common\\Collections\\ArrayCollection',
                'format'    => 'json',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\Common\\Collections\\ArrayCollection',
                'format'    => 'json',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\Common\\Collections\\ArrayCollection',
                'format'    => 'xml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\Common\\Collections\\ArrayCollection',
                'format'    => 'xml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\Common\\Collections\\ArrayCollection',
                'format'    => 'yml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\Common\\Collections\\ArrayCollection',
                'format'    => 'yml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ORM\\PersistentCollection',
                'format'    => 'json',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ORM\\PersistentCollection',
                'format'    => 'json',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ORM\\PersistentCollection',
                'format'    => 'xml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ORM\\PersistentCollection',
                'format'    => 'xml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ORM\\PersistentCollection',
                'format'    => 'yml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ORM\\PersistentCollection',
                'format'    => 'yml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ODM\\MongoDB\\PersistentCollection',
                'format'    => 'json',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ODM\\MongoDB\\PersistentCollection',
                'format'    => 'json',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ODM\\MongoDB\\PersistentCollection',
                'format'    => 'xml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ODM\\MongoDB\\PersistentCollection',
                'format'    => 'xml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ODM\\MongoDB\\PersistentCollection',
                'format'    => 'yml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ODM\\MongoDB\\PersistentCollection',
                'format'    => 'yml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ODM\\PHPCR\\PersistentCollection',
                'format'    => 'json',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ODM\\PHPCR\\PersistentCollection',
                'format'    => 'json',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ODM\\PHPCR\\PersistentCollection',
                'format'    => 'xml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ODM\\PHPCR\\PersistentCollection',
                'format'    => 'xml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ODM\\PHPCR\\PersistentCollection',
                'format'    => 'yml',
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ODM\\PHPCR\\PersistentCollection',
                'format'    => 'yml',
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'ArrayCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'ArrayCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\Common\\Collections\\ArrayCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\Common\\Collections\\ArrayCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ORM\\PersistentCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ORM\\PersistentCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ODM\\MongoDB\\PersistentCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ODM\\MongoDB\\PersistentCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'deserializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => 'Doctrine\\ODM\\PHPCR\\PersistentCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'serializeCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => 'Doctrine\\ODM\\PHPCR\\PersistentCollection',
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'deserializeCollection',
            ]
        ];
    }
}
