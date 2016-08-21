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

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer as FosRestSerializerInterface;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext as JMSSerializationContext;
use JMS\Serializer\DeserializationContext as JMSDeserializationContext;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class Serializer implements FosRestSerializerInterface
{
    /**
     * @internal
     */
    const SERIALIZATION = 0;
    /**
     * @internal
     */
    const DESERIALIZATION = 1;

    private $serializer;

    /**
     * @var ExclusionStrategyInterface[]
     */
    protected $exclusionStrategies;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $exclusionStrategies
     */
    public function setExclusionStrategies($exclusionStrategies)
    {
        $this->exclusionStrategies = $exclusionStrategies;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, Context $context = null)
    {
        $context = $this->convertContext($context, self::SERIALIZATION, $format);

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, Context $context)
    {
        $context = $this->convertContext($context, self::DESERIALIZATION, $format);

        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    /**
     * @param Context $context
     * @param int     $direction {@see self} constants
     *
     * @return JMSContext
     */
    private function convertContext(Context $context, $direction, $format)
    {
        if ($direction === self::SERIALIZATION) {
            $jmsContext = JMSSerializationContext::create();
        } else {
            $jmsContext = JMSDeserializationContext::create();
            $maxDepth = $context->getMaxDepth();
            if (null !== $maxDepth) {
                for ($i = 0; $i < $maxDepth; ++$i) {
                    $jmsContext->increaseDepth();
                }
            }
        }

        foreach ($context->getAttributes() as $key => $value) {
            $jmsContext->attributes->set($key, $value);
        }

        if (null !== $context->getVersion()) {
            $jmsContext->setVersion($context->getVersion());
        }
        $groups = $context->getGroups();
        if (!empty($groups)) {
            $jmsContext->setGroups($context->getGroups());
        }
        if (null !== $context->getMaxDepth()) {
            $jmsContext->enableMaxDepthChecks();
        }
        if (null !== $context->getSerializeNull()) {
            $jmsContext->setSerializeNull($context->getSerializeNull());
        }

        if ($format === 'json') {
            foreach ($this->exclusionStrategies as $exclusionStrategy) {
                $jmsContext->addExclusionStrategy($exclusionStrategy);
            }
        }

        return $jmsContext;
    }
}
