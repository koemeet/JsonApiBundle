<?php
/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver;

use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;
use Mango\Bundle\JsonApiBundle\Util\StringUtil;
use Metadata\Driver\AbstractFileDriver;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class YamlDriver extends AbstractFileDriver
{
    /**
     * {@inheritdoc}
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $config = Yaml::parse(file_get_contents($file));

        if (!isset($config[$name = $class->getName()])) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $name, $file));
        }

        $config = $config[$name];

        if (isset($config['resource'])) {
            $classMetadata = new ClassMetadata($name);
            $classMetadata->fileResources[] = $file;
            $classMetadata->fileResources[] = $class->getFileName();

            $classMetadata->setResource($this->parseResource($config, $class));

            if (isset($config['resource']['idField'])) {
                $classMetadata->setIdField(trim($config['resource']['idField']));
            }

            if (isset($config['relations'])) {
                foreach ($config['relations'] as $name => $relation) {
                    $classMetadata->addRelationship(new Relationship(
                        $name,
                        (isset($relation['includeByDefault'])) ? $relation['includeByDefault'] : null,
                        (isset($relation['showData'])) ? $relation['showData'] : null,
                        (isset($relation['showLinkSelf'])) ? $relation['showLinkSelf'] : null,
                        (isset($relation['showLinkRelated'])) ? $relation['showLinkRelated'] : null
                    ));
                }
            }

            return $classMetadata;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'yml';
    }

    /**
     * @param array            $config
     * @param \ReflectionClass $class
     *
     * @return Resource
     */
    protected function parseResource(array $config, \ReflectionClass $class)
    {
        if (isset($config['resource'])) {
            $resource = $config['resource'];

            return new Resource(
                $resource['type'],
                isset($resource['showLinkSelf']) ? $resource['showLinkSelf'] : null
            );
        }

        return new Resource(StringUtil::dasherize($class->getShortName()));
    }
}
