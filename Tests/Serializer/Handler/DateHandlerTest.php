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
use Mango\Bundle\JsonApiBundle\Serializer\Handler\DateHandler;
use Mango\Bundle\JsonApiBundle\Tests\TestCase;

/**
 * DateHandlerTest test
 *
 * @author Alexander Kurbatsky <alexander.kurbatsky@intexsys.lv>
 */
class DateHandlerTest extends TestCase
{
    /**
     * Test getSubscribingMethods method
     *
     * @return void
     */
    public function testGetSubscribingMethods()
    {
        $this->assertEquals($this->getExpectedSubscribingMethods(), DateHandler::getSubscribingMethods());
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
                'type'      => 'DateTime',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
            ],
            [
                'type'      => 'DateTimeImmutable',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
            ],
            [
                'type'      => 'DateInterval',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
            ],
            [
                'type'      => 'DateTime',
                'format'    => 'json',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateTime',
            ],
            [
                'type'      => 'DateTimeImmutable',
                'format'    => 'json',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateTimeImmutable',
            ],
            [
                'type'      => 'DateInterval',
                'format'    => 'json',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateInterval',
            ],
            [
                'type'      => 'DateTime',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
            ],
            [
                'type'      => 'DateTimeImmutable',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
            ],
            [
                'type'      => 'DateInterval',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
            ],
            [
                'type'      => 'DateTime',
                'format'    => 'xml',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateTime',
            ],
            [
                'type'      => 'DateTimeImmutable',
                'format'    => 'xml',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateTimeImmutable',
            ],
            [
                'type'      => 'DateInterval',
                'format'    => 'xml',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateInterval',
            ],
            [
                'type'      => 'DateTime',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'yml',
            ],
            [
                'type'      => 'DateTimeImmutable',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'yml',
            ],
            [
                'type'      => 'DateInterval',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'yml',
            ],
            [
                'type'      => 'DateTime',
                'format'    => 'yml',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateTime',
            ],
            [
                'type'      => 'DateTimeImmutable',
                'format'    => 'yml',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateTimeImmutable',
            ],
            [
                'type'      => 'DateInterval',
                'format'    => 'yml',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateInterval',
            ],
            [
                'type'      => 'DateTime',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'deserializeDateTimeFromJson',
            ],
            [
                'type'      => 'DateTimeImmutable',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'deserializeDateTimeImmutableFromJson',
            ],
            [
                'type'      => 'DateInterval',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'deserializeDateIntervalFromJson',
            ],
            [
                'type'      => 'DateTime',
                'format'    => MangoJsonApiBundle::FORMAT,
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateTime',
            ],
            [
                'type'      => 'DateTimeImmutable',
                'format'    => MangoJsonApiBundle::FORMAT,
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateTimeImmutable',
            ],
            [
                'type'      => 'DateInterval',
                'format'    => MangoJsonApiBundle::FORMAT,
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serializeDateInterval',
            ]
        ];
    }
}
