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
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Metadata\MetadataFactoryInterface;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class JsonApiSerializationVisitor extends JsonSerializationVisitor
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @var bool
     */
    protected $showVersionInfo;

    /**
     * @param PropertyNamingStrategyInterface $propertyNamingStrategy
     * @param MetadataFactoryInterface        $metadataFactory
     * @param                                 $showVersionInfo
     */
    public function __construct(
        PropertyNamingStrategyInterface $propertyNamingStrategy,
        MetadataFactoryInterface $metadataFactory,
        $showVersionInfo)
    {
        parent::__construct($propertyNamingStrategy);

        $this->metadataFactory = $metadataFactory;
        $this->showVersionInfo = $showVersionInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        $result = $this->getRoot();

        if ($result) {
            $meta = null;
            $included = null;
            $links = null;

            // strip out included part, since it does not belong to the primary resource data
            if (isset($result['included'])) {
                $included = $result['included'];
                unset($result['included']);
            }

            if (isset($result['meta'])) {
                $meta = $result['meta'];
                unset($result['meta']);
            }

            if (isset($result['links'])) {
                $links = $result['links'];
                unset($result['links']);
            }

            // filter out duplicate primary resource objects that are in `included`
            $included = array_udiff((array)$included, $result, function ($a, $b) {
                return strcmp($a['type'] . $a['id'], $b['type'] . $b['id']);
            });

            $root = array();

            if ($this->showVersionInfo) {
                $root['jsonapi'] = array(
                    'version' => '1.0'
                );
            }

            if ($meta) {
                $root['meta'] = $meta;
            }

            if ($links) {
                $root['links'] = $links;
            }

            $root['data'] = array_values($result);

            if ($included) {
                $root['included'] = array_values($included);
            }

            $this->setRoot($root);
        }

        return parent::getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function endVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        /** @var JsonApiClassMetadata $jsonApiMetadata */
        $jsonApiMetadata = $this->metadataFactory->getMetadataForClass(get_class($data));
        $idField = $jsonApiMetadata->getIdField();

        $rs = parent::endVisitingObject($metadata, $data, $type, $context);

        if (empty($rs)) {
            $rs = new \ArrayObject();

            if (array() === $this->getRoot()) {
                $this->setRoot(clone $rs);
            }

            return $rs;
        }

        $result = array();

        if (isset($rs['type'])) {
            $result['type'] = $rs['type'];
        }

        if (isset($rs[$idField])) {
            $result['id'] = $rs[$idField];
        }

        $result['attributes'] = array_filter($rs, function ($key) use ($idField) {
            switch ($key) {
                case $idField:
                case 'type':
                case 'relationships':
                case 'links':
                    return false;
            }
            return true;
        }, ARRAY_FILTER_USE_KEY);

        if (isset($rs['relationships'])) {
            $result['relationships'] = $rs['relationships'];
        }

        if (isset($rs['links'])) {
            $result['links'] = $rs['links'];
        }

        return $result;
    }
}
