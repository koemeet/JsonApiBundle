<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception handler
 *
 * @author Sergey Chernecov <sergey.chernecov@gmail.com>
 */
class ExceptionHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => \Exception::class,
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'serializeException'
            ]
        ];
    }

    /**
     * Serialize exception
     * @see http://jsonapi.org/format/#error-objects
     *
     * @param JsonSerializationVisitor $visitor
     * @param \Exception               $exception
     *
     * @return array
     */
    public function serializeException(JsonSerializationVisitor $visitor, \Exception $exception)
    {
        $data = [
            // all these values should be a string according to spec
            'status' => (string) Response::HTTP_BAD_REQUEST,
            'code'   => (string) $exception->getCode(),
            'title'  => 'Exception has been thrown',
            'detail' => (string) $exception->getMessage()
        ];

        if (null === $visitor->getRoot()) {
            $visitor->setRoot($data);
        }

        return $data;
    }
}
