<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Fixtures;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver\AnnotationDriver;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver\YamlDriver;
use Mango\Bundle\JsonApiBundle\EventListener\Serializer\JsonEventSubscriber;
use Mango\Bundle\JsonApiBundle\Resolver\BaseUri\BaseUriResolver;
use Mango\Bundle\JsonApiBundle\Serializer\Exclusion\RelationshipExclusionStrategy;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiDeserializationVisitor;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Mango\Bundle\JsonApiBundle\Serializer\Serializer as JsonApiSerializer;
use Mango\Bundle\JsonApiBundle\Tests\Cache\NoopCache;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use PhpCollection\Map;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Json api serializer builder
 *
 * @author Ruslan Zavacky <ruslan.zavacky@gmail.com>
 */
class JsonApiSerializerBuilder
{
    /**
     * Build
     *
     * @return JsonApiSerializer
     */
    public static function build()
    {
        $drivers = [
            new YamlDriver(new FileLocator(['Mango\Bundle\JsonApiBundle\Tests\Fixtures' => __DIR__ . '/yml'])),
            new AnnotationDriver(new AnnotationReader())
        ];

        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy('-'));
        $jmsMetadataFactory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $jsonApiChainDriver = new DriverChain($drivers);

        $jsonApiMetadataFactory = new MetadataFactory($jsonApiChainDriver);
        $jsonApiMetadataFactory->setCache(new NoopCache());
        $handlerRegistry = new HandlerRegistry();

        $jsonApiEventSubscriber = new JsonEventSubscriber(
            $jsonApiMetadataFactory,
            $jmsMetadataFactory,
            $namingStrategy,
            new RequestStack(),
            new BaseUriResolver('/')
        );

        $doctrineProxySubscriber = new Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber();

        $dispatcher = new Serializer\EventDispatcher\EventDispatcher();
        $dispatcher->addSubscriber($doctrineProxySubscriber);
        $dispatcher->addSubscriber($jsonApiEventSubscriber);

        $accessorStrategy = new Serializer\Accessor\DefaultAccessorStrategy();

        $jsonApiSerializationVisitor = new JsonApiSerializationVisitor(
            $namingStrategy,
            $accessorStrategy,
            $jmsMetadataFactory
        );
        $jsonApiDeserializationVisitor = new JsonApiDeserializationVisitor($namingStrategy);

        $serializationVisitors = new Map(['json' => $jsonApiSerializationVisitor]);
        $deserializationVisitors = new Map(['json' => $jsonApiDeserializationVisitor]);
        $objectConstructor = new Serializer\Construction\UnserializeObjectConstructor();

        $jmsSerializer = new Serializer\Serializer(
            $jmsMetadataFactory,
            $handlerRegistry,
            $objectConstructor,
            $serializationVisitors,
            $deserializationVisitors,
            $dispatcher
        );

        $exclusionStrategy = new RelationshipExclusionStrategy($jmsMetadataFactory);

        return new JsonApiSerializer($jmsSerializer, $exclusionStrategy);
    }
}
