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
     *
     * @param JsonSerializationVisitor $visitor
     * @param \Exception               $exception
     *
     * @return array
     */
    public function serializeException(JsonSerializationVisitor $visitor, \Exception $exception)
    {
        $data = [
            'status' => Response::HTTP_BAD_REQUEST,
            'code'   => $exception->getCode(),
            'title'  => 'Exception has been thrown',
            'detail' => $exception->getMessage()
        ];

        if (null === $visitor->getRoot()) {
            $visitor->setRoot($data);
        }

        return $data;
    }
}
