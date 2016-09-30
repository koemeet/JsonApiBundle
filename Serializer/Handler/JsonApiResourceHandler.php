<?php

namespace AppBundle\Serializer\Handler;

use AppBundle\JsonApi\CachedResult;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Psr\Cache\CacheItemPoolInterface;

class JsonApiResourceHandler implements SubscribingHandlerInterface
{
    /**
     * 
     */
    public function __construct()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'array',
                'method' => 'deserialize'
            ]
        ];
    }

    public function deserialize(
        JsonApiSerializationVisitor $visitor,
        array $data,
        array $type,
        Context $context
    )
    {
        if (!isset($data['id']) && !isset($data['type'])) {
            return $visitor->visitArray($data, $type, $context);
        }

        $id = $data['id'];
        $type = $data['type'];
    }
}
