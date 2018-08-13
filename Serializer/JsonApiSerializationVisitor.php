<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer;

use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\Common\Proxy\Proxy as ORMProxy;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use Mango\Bundle\JsonApiBundle\EventListener\Serializer\JsonEventSubscriber;
use Metadata\MetadataFactoryInterface;
use PhpOption\None;
use Symfony\Component\ExpressionLanguage;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
     * @var bool
     */
    protected $isJsonApiDocument = false;

    /**
     * @param PropertyNamingStrategyInterface $propertyNamingStrategy
     * @param AccessorStrategyInterface|null  $accessorStrategy
     * @param MetadataFactoryInterface|null   $metadataFactory
     * @param bool                            $showVersionInfo
     */
    public function __construct(
        PropertyNamingStrategyInterface $propertyNamingStrategy,
        AccessorStrategyInterface $accessorStrategy = null,
        MetadataFactoryInterface $metadataFactory = null,
        $showVersionInfo = false
    ) {
        parent::__construct(
            $propertyNamingStrategy,
            $accessorStrategy
        );

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
     * @param mixed $root
     *
     * @return array
     */
    public function prepare($root)
    {
        if (is_array($root) && array_key_exists('data', $root)) {
            $data = $root['data'];
        } else {
            $data = $root;
        }

        if (($data instanceof \Traversable) && count($data) === 0 && !$this->isResource($data)) {
            $data = [];
        }

        $this->isJsonApiDocument = $this->validateJsonApiDocument($data);

        if ($this->isJsonApiDocument) {
            $meta = null;
            if (is_array($root) && isset($root['meta']) && is_array($root['meta'])) {
                $meta = $root['meta'];
            }

            return $this->buildJsonApiRoot($data, $meta);
        }

        return $root;
    }

    /**
     * Build json api root
     *
     * @param mixed      $data
     * @param array|null $meta
     *
     * @return array
     */
    protected function buildJsonApiRoot($data, array $meta = null)
    {
        if ($data instanceof ConstraintViolationListInterface) {
            $root = [
                'errors' => $data,
            ];
        } elseif ($data instanceof \Exception) {
            $root = [
                'errors' => [$data],
            ];
        } else {
            $root = [
                'data' => $data,
            ];
        }

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
        if (is_null($data)) {
            return true;
        }

        if (is_array($data)) {
            return true;
        }

        if ((is_array($data) || $data instanceof \Traversable) && count($data) > 0 && $this->hasResource($data)) {
            return true;
        }

        if ($data instanceof ConstraintViolationListInterface) {
            return true;
        }

        if ($data instanceof \Exception) {
            return true;
        }

        return $this->isResource($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        if (false === $this->isJsonApiDocument) {
            return parent::getResult();
        }

        $root = $this->getRoot();

        if ($root) {
            $data = array();
            $meta = array();
            $included = array();
            $links = array();
            $errors = array();

            if (array_key_exists('data', $root)) {
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

            if (isset($root['errors'])) {
                $errors = $root['errors'];
            }

            if (!is_null($data)) {
                // filter out duplicate primary resource objects that are in `included`
                $included = array_udiff(
                    (array) $included,
                    (isset($data['type'])) ? array($data) : $data,
                    function ($a, $b) {
                        return strcmp(
                            $a['type'] . $a['id'],
                            $b['type'] . $b['id']
                        );
                    }
                );
            }

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

            if (is_array($errors) && count($errors) > 0) {
                $root['errors'] = $errors;
            } else {
                $root['data'] = $data;
                if ($included) {
                    $root['included'] = array_values($included);
                }
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
            $rs = array();
            $this->setRoot($rs);

            return $rs;
        }

        if ($data instanceof Proxy || $data instanceof ORMProxy) {
            $class = get_parent_class($data);
        } else {
            $class = get_class($data);
        }

        /** @var JsonApiClassMetadata $jsonApiMetadata */
        $jsonApiMetadata = $this->metadataFactory->getMetadataForClass($class);

        if (null === $jsonApiMetadata) {
            return $rs;
        }

        $result = array();

        if (isset($rs[JsonEventSubscriber::EXTRA_DATA_KEY]['type'])) {
            $language = new ExpressionLanguage\ExpressionLanguage();
            $type =  $rs[JsonEventSubscriber::EXTRA_DATA_KEY]['type'];

            $groups = $context->attributes->get('groups');
            $groups = $groups instanceof None ? [] : $groups->get();

            try {
                $result['type'] = $language->evaluate($type, ['groups' => $groups]);
            } catch (ExpressionLanguage\SyntaxError $e) {
                $result['type'] = $type;
            }
        }

        if (isset($rs[JsonEventSubscriber::EXTRA_DATA_KEY]['id'])) {
            $result['id'] = $rs[JsonEventSubscriber::EXTRA_DATA_KEY]['id'];
        }

        $idField = $jsonApiMetadata->getIdField();

        $result['attributes'] = array_filter(
            $rs,
            function ($key) use ($idField) {
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
            },
            ARRAY_FILTER_USE_KEY
        );

        if (isset($rs['relationships'])) {
            $result['relationships'] = $rs['relationships'];
        }

        if (isset($rs['links'])) {
            $result['links'] = $rs['links'];
        }

        return $result;
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
        if (is_object($data)) {
            if ($data instanceof Proxy || $data instanceof ORMProxy) {
                $class = get_parent_class($data);
            } else {
                $class = get_class($data);
            }

            /** @var JsonApiClassMetadata $metadata */
            if ($metadata = $this->metadataFactory->getMetadataForClass($class)) {
                if ($metadata->getResource()) {
                    return true;
                }
            }
        }

        return false;
    }
}
