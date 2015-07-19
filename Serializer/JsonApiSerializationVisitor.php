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
     * @param mixed $data
     *
     * @return array
     */
    public function prepare($data)
    {
        return array(
            'data' => $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        $root = $this->getRoot();

        // TODO: Error handling
        if (isset($root['data']) && array_key_exists('errors', $root['data'])) {
            return parent::getResult();
        }

        if ($root) {
            $data = array();
            $meta = array();
            $included = array();
            $links = array();

            if (isset($root['data'])) {
                $data = $root['data'];
            }

            if (isset($root['included'])) {
                $included = $root['included'];
            }

            if (isset($root['meta'])) {
                $meta = $root['meta'];
            }

            if (isset($root['links'])) {
                $links = $root['links'];
            }

            // filter out duplicate primary resource objects that are in `included`
            $included = array_udiff(
                (array)$included,
                (isset($data['type'])) ? array($data) : $data,
                function ($a, $b) {
                    return strcmp($a['type'] . $a['id'], $b['type'] . $b['id']);
                }
            );

            // start building new root array
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

            $root['data'] = $data;


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
        $rs = parent::endVisitingObject($metadata, $data, $type, $context);

        /** @var JsonApiClassMetadata $jsonApiMetadata */
        $jsonApiMetadata = $this->metadataFactory->getMetadataForClass(get_class($data));

        if (null === $jsonApiMetadata) {
            return $rs;
        }

        $idField = $jsonApiMetadata->getIdField();

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
