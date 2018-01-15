<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Constraint violation handler
 *
 * @author Sergey Chernecov <sergey.chernecov@gmail.com>
 */
class ConstraintViolationHandler implements SubscribingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => ConstraintViolationList::class,
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'serializeConstraintViolationList'
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => ConstraintViolation::class,
                'format'    => MangoJsonApiBundle::FORMAT,
                'method'    => 'serializeConstraintViolation'
            ]
        ];
    }

    /**
     * Serialize constraint violation list
     *
     * @param JsonSerializationVisitor $visitor
     * @param ConstraintViolationList  $list
     * @param array                    $type
     * @param Context                  $context
     *
     * @return array
     */
    public function serializeConstraintViolationList(JsonSerializationVisitor $visitor, ConstraintViolationList $list, array $type, Context $context)
    {
        return $visitor->visitArray(iterator_to_array($list), $type, $context);
    }

    /**
     * Serialize constraint violation
     *
     * @param JsonSerializationVisitor $visitor
     * @param ConstraintViolation      $violation
     *
     * @return array
     */
    public function serializeConstraintViolation(JsonSerializationVisitor $visitor, ConstraintViolation $violation)
    {
        $data = [
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'code'   => $violation->getCode(),
            'title'  => 'Validation Failed',
            'detail' => $violation->getMessage(),
            'meta' => [
                'property_path' => $violation->getPropertyPath(),
                'invalid_value' => $violation->getInvalidValue()
            ]
        ];

        if (null === $visitor->getRoot()) {
            $visitor->setRoot($data);
        }

        return $data;
    }
}
