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

use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class JsonApiSerializationVisitor extends JsonSerializationVisitor
{
    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        $result = $this->getRoot();

        $included = null;

        // strip out included part, since it does not belong to the primary resource data
        if (isset($result['included'])) {
            $included = $result['included'];
            unset($result['included']);
        }

        // filter out duplicate primary resource objects that are in `included`
        $included = array_udiff($included, $result, function ($a, $b) {
            return strcmp($a['type'] . $a['id'], $b['type'] . $b['id']);
        });

        $root = array(
            'data' => array_values($result)
        );

        if ($included) {
            $root['included'] = array_values($included);
        }

        $this->setRoot($root);

        return parent::getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function endVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        $rs = parent::endVisitingObject($metadata, $data, $type, $context);

        $result = array();

        if (isset($rs['type'])) {
            $result['type'] = $rs['type'];
        }

        if (isset($rs['id'])) {
            $result['id'] = $rs['id'];
        }

        $result['attributes'] = array_filter($rs, function ($key) {
            switch ($key) {
                case 'id':
                case 'type':
                case 'relationships':
                    return false;
            }
            return true;
        }, ARRAY_FILTER_USE_KEY);

        if (isset($rs['relationships'])) {
            $result['relationships'] = $rs['relationships'];
        }

        return $result;
    }
}
