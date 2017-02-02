<?php
/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\scalar;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
final class Serializer implements SerializerInterface
{
    /**
     * @var SerializerInterface
     */
    private $jmsSerializer;

    /**
     * @var ExclusionStrategyInterface
     */
    private $exclusionStrategy;

    public function __construct(SerializerInterface $jmsSerializer, ExclusionStrategyInterface $exclusionStrategy)
    {
        $this->jmsSerializer = $jmsSerializer;
        $this->exclusionStrategy = $exclusionStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, SerializationContext $context = null)
    {
        if (null === $context) {
            $context = new SerializationContext();
        }

        if ($format === 'json') {
            $context->addExclusionStrategy($this->exclusionStrategy);
        }

        return $this->jmsSerializer->serialize($data, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function deserialize($data, $type, $format, DeserializationContext $context = null)
    {
        return $this->jmsSerializer->deserialize($data, $type, $format, $context);
    }
}
