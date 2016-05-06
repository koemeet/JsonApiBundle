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

use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Mango\Bundle\JsonApiBundle\EventListener\Serializer\JsonEventSubscriber;
use Mango\Bundle\JsonApiBundle\Serializer\Handler\HateoasRepresentationHandler;
use Mango\Bundle\JsonApiBundle\Serializer\Handler\PagerfantaHandler;
use Metadata\MetadataFactoryInterface;
use Pagerfanta\Pagerfanta;

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

    protected $isJsonApiDocument = false;

    protected $isJsonApiErrorDocument = false;

    /**
     * @param PropertyNamingStrategyInterface $propertyNamingStrategy
     * @param MetadataFactoryInterface        $metadataFactory
     * @param                                 $showVersionInfo
     */
    public function __construct(
        PropertyNamingStrategyInterface $propertyNamingStrategy,
        MetadataFactoryInterface $metadataFactory,
        $showVersionInfo
    ) {
        parent::__construct($propertyNamingStrategy);

        $this->metadataFactory = $metadataFactory;
        $this->showVersionInfo = $showVersionInfo;
    }

    /**
     * @return bool
     */
    public function isJsonApiDocument()
    {
        return $this->isJsonApiDocument;
    }

    /**
     * @return bool
     */
    public function isJsonApiErrorDocument()
    {
        return $this->isJsonApiErrorDocument;
    }

    /**
     * @param mixed $root
     *
     * @return array
     */
    public function prepare($root)
    {
        if ($this->isJsonApiDocument = $this->validateJsonApiDocument($root)) {
            return $this->prepareResource($root);
        } elseif ($this->isJsonApiErrorDocument = $this->validateJsonApiErrorDocument($root)) {
            return $this->prepareErrors($root);
        }

        return $root;
    }
    /**
     * @param mixed $root
     *
     * @return array
     */
    protected function prepareResource($root)
    {
        if (is_array($root) && array_key_exists('data', $root)) {
            $data = $root['data'];
        } else {
            $data = $root;
        }

        $meta = null;
        if (is_array($root) && isset($root['meta']) && is_array($root['meta'])) {
            $meta = $root['meta'];
        }

        return $this->buildJsonApiRoot($data, $meta);
    }

    /**
     * @param mixed $root
     *
     * @return array
     */
    protected function prepareErrors($root)
    {
        if (is_array($root) && array_key_exists('errors', $root)) {
            $errors = $root['errors'];
        } else {
            $errors = $root;
        }

        if (is_object($errors)) {
            $errors = array($errors);
        }

        return $this->buildJsonApiRoot($errors);
    }

    protected function buildJsonApiRoot($data, array $meta = null)
    {
        $key = 'unknown';

        if ($this->isJsonApiDocument) {
            $key = 'data';
        }

        if ($this->isJsonApiErrorDocument) {
            $key = 'errors';
        }

        $root = array(
            $key    => $data,
        );

        if ($meta) {
            $root['meta'] = $meta;
        }

        return $root;
    }

    /**
     * it is a JSON-API document if:
     *  - it is an object and is a JSON-API resource
     *  - it is an array containing objects which are JSON-API resources
     *  - it is empty (we cannot identify it)
     *
     * @param mixed $data
     *
     * @return bool
     */
    protected function validateJsonApiDocument($data)
    {
        if (!$this->isPaginator($data) && !$this->isResource($data)) {
            return false;
        }

        if ($this->isPaginator($data) && !$this->hasResource($data)) {
            return false;
        }

        if (is_array($data) && count($data) > 0 && !$this->hasResource($data)) {
            return false;
        }

        return true;
    }

    /**
     * it is a JSON-API document if:
     *  - it is an object and is a JSON-API resource
     *  - it is an array containing objects which are JSON-API resources
     *  - it is empty (we cannot identify it)
     *
     * @param mixed $errors
     *
     * @return bool
     */
    protected function validateJsonApiErrorDocument($errors)
    {
        if (!$this->isPaginator($errors) && !$this->isError($errors)) {
            return false;
        }

        if ($this->isPaginator($errors) && !$this->hasErrors($errors)) {
            return false;
        }

        if (is_array($errors) && count($errors) > 0 && !$this->hasErrors($errors)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        if ($this->isJsonApiErrorDocument) {
            if ($this->showVersionInfo) {
                $root = $this->getRoot();

                $root['jsonapi'] = array(
                    'version' => '1.0',
                );

                $this->setRoot($root);
            }
        }

        if (false === $this->isJsonApiDocument) {
            return parent::getResult();
        }

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
                (isset($data['type'])) ? [$data] : $data,
                function($a, $b) {
                    return strcmp($a['type'].$a['id'], $b['type'].$b['id']);
                }
            );

            // start building new root array
            $root = array();

            if ($this->showVersionInfo) {
                $root['jsonapi'] = array(
                    'version' => '1.0',
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

        if ($rs instanceof \ArrayObject) {
            $rs = [];
            $this->setRoot($rs);

            return $rs;
        }

        $jsonApiMetadata = $this->getMetadataForClass($data);

        if (null !== $jsonApiMetadata && $jsonApiMetadata->getResource()) {
            return $this->endVisitingResource($jsonApiMetadata, $rs);
        } elseif (null !== $jsonApiMetadata && $jsonApiMetadata->isError()) {
            return $this->endVisitingError($jsonApiMetadata, $rs);
        }

        return $rs;
    }

    /**
     * {@inheritdoc}
     */
    protected function endVisitingResource(JsonApiClassMetadata $metadata, $rs)
    {
        if (!$metadata->getResource()) {
            return $rs;
        }

        $result = array();

        if (isset($rs[JsonEventSubscriber::EXTRA_DATA_KEY]['type'])) {
            $result['type'] = $rs[JsonEventSubscriber::EXTRA_DATA_KEY]['type'];
        }

        if (isset($rs[JsonEventSubscriber::EXTRA_DATA_KEY]['id'])) {
            $result['id'] = $rs[JsonEventSubscriber::EXTRA_DATA_KEY]['id'];
        }

        $idField = $metadata->getIdField();

        $result['attributes'] = array_filter($rs, function($key) use ($idField) {
            switch ($key) {
                case $idField:
                case 'relationships':
                case 'links':
                    return false;
            }

            if ($key === JsonEventSubscriber::EXTRA_DATA_KEY) {
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

    /**
     * {@inheritdoc}
     */
    protected function endVisitingError(JsonApiClassMetadata $metadata, $rs)
    {
        if (!$metadata->isError()) {
            return $rs;
        }

        $result = array_filter($rs, function($key) {
            return in_array($key, ['id', 'title', 'detail', 'meta']);
        }, ARRAY_FILTER_USE_KEY);

        if (isset($rs['status'])) {
            $result['status'] = (string) $rs['status'];
        }

        if (isset($rs['code'])) {
            $result['code'] = (string) $rs['code'];
        }

        if (isset($rs['source'])) {
            $result['source'] = array_filter($rs['source'], function($key) {
                return in_array($key, ['pointer', 'parameter']);
            }, ARRAY_FILTER_USE_KEY);
        }

        if (isset($result['source']) && empty($result['source'])) {
            unset($result['source']);
        }

        if (isset($rs['links'])) {
            $result['links'] = array_filter($rs['links'], function($key) {
                return 'about' === $key;
            }, ARRAY_FILTER_USE_KEY);
        }

        return $result;
    }

    /**
     * @param mixed $data
     * @return JsonApiClassMetadata $metadata
     */
    protected function getMetadataForClass($data)
    {
        if (!is_object($data)) {
            return false;
        }

        return $this->metadataFactory->getMetadataForClass(get_class($data));
    }

    /**
     * @param $items
     *
     * @return bool
     */
    protected function hasResource($items)
    {
        foreach ($items as $item) {
            return $this->isResource($item);
        }

        return false;
    }

    /**
     * Check if the given variable is a valid JSON-API resource.
     *
     * @param $data
     *
     * @return bool
     */
    protected function isResource($data)
    {
        if ($metadata = $this->getMetadataForClass($data)) {
            return $metadata->getResource();
        }

        return false;
    }

    /**
     * @param $items
     *
     * @return bool
     */
    protected function hasErrors($items)
    {
        foreach ($items as $item) {
            return $this->isError($item);
        }

        return false;
    }

    /**
     * Check if the given variable is a valid JSON-API error.
     *
     * @param $error
     *
     * @return bool
     */
    protected function isError($error)
    {
        if ($metadata = $this->getMetadataForClass($error)) {
            return $metadata->isError();
        }

        return false;
    }

    /**
     * Is data a paginated object with resource(s)
     *
     * @param mixed $data
     * @return bool
     */
    protected function isPaginator($data)
    {
        if (!is_object($data)) {
            return false;
        }

        $paginatedClasses = array(
            PagerfantaHandler::getType(),
            HateoasRepresentationHandler::getType(),
        );

        foreach ($paginatedClasses as $paginatedClass) {
            if (is_a($data, $paginatedClass)) {
                return true;
            }
        }

        return false;
    }
}
