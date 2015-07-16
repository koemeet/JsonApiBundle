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

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer as BaseSerializer;
use Pagerfanta\Pagerfanta;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class Serializer extends BaseSerializer
{
    /**
     * @var ExclusionStrategyInterface[]
     */
    protected $exclusionStrategies;

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
    public function serialize($data, $format, SerializationContext $context = null)
    {
        if (null === $context) {
            $context = new SerializationContext();
        }

        if ($format === 'json') {
            foreach ($this->exclusionStrategies as $exclusionStrategy) {
                $context->addExclusionStrategy($exclusionStrategy);
            }
        }

        return parent::serialize($data, $format, $context);
    }
}
