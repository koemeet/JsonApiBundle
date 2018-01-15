<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;

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

    /**
     * @param SerializerInterface        $jmsSerializer
     * @param ExclusionStrategyInterface $exclusionStrategy
     */
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

        if ($format === MangoJsonApiBundle::FORMAT) {
            $context->addExclusionStrategy($this->exclusionStrategy);
            $context->setSerializeNull(true);
        }

        return $this->jmsSerializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, DeserializationContext $context = null)
    {
        if (null === $context) {
            $context = new DeserializationContext();
        }

        return $this->jmsSerializer->deserialize($data, $type, $format, $context);
    }
}
