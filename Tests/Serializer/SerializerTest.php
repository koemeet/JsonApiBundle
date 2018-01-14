<?php

/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Serializer;

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
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderAddress;
use Mango\Bundle\JsonApiBundle\Tests\TestCase;
use Metadata\Cache\FileCache;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use PhpCollection\Map;
use Symfony\Component\HttpFoundation\RequestStack;

class SerializerTest extends TestCase
{
    /** @var JsonApiSerializer */
    protected $jsonApiSerializer;

    public function testDeserializeId()
    {
        $id = 'ORDER-1';
        $data = json_encode(['data' => ['id' => $id]]);

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            'json',
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($order->getId(), $id);
    }

    public function testDeserializeAttribute()
    {
        $id = 'ORDER-1';
        $email = 'hello@example.com';

        $data = json_encode(['data' => ['id' => $id, 'attributes' => ['email' => $email]]]);

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            'json',
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($order->getId(), $id);
        $this->assertSame($order->getEmail(), $email);
    }

    public function testDeserializeDasherizedAttributes()
    {
        $id = 'ORDER-1';
        $adminComments = 'Admin comment';

        $data = json_encode(['data' => ['id' => $id, 'attributes' => ['admin-comments' => $adminComments]]]);

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            'json',
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($order->getId(), $id);
        $this->assertSame($order->getAdminComments(), $adminComments);
    }

    public function testDeserializeSingleRelationship()
    {
        $id = 'ORDER-1';
        $addressId = 'ORDER-ADDRESS-1';
        $addressStreet = 'Address street';

        $data = json_encode([
            'data' => [
                'id' => $id,
                'relationships' => [
                    'address' => [
                        'data' => [
                            'type' => 'order/address',
                            'id' => $addressId,
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'order/address',
                    'id' => $addressId,
                    'attributes' => [
                        'street' => $addressStreet,
                    ],
                ],
            ],
        ]);

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            'json',
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($id, $order->getId());
        $this->assertSame(true, $order->getAddress() instanceof OrderAddress);
        $this->assertSame($addressStreet, $order->getAddress()->getStreet());
    }

    public function testSimpleSerialize()
    {
        $order = new Order();
        $order->setId(1);
        $order->setEmail('test@example.com');
        $order->setPhone('+440000000000');
        $order->setAdminComments('Test comments that might be longer that ordinary text.');
        $order->setAddress(null);

        $serialized = $this->jsonApiSerializer->serialize(
            $order,
            'json',
            Serializer\SerializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame(json_decode($serialized, 1), [
            'data' => [
                'type' => 'order',
                'id' => 1,
                'attributes' => [
                    'email' => 'test@example.com',
                    'phone' => '+440000000000',
                    'admin-comments' => 'Test comments that might be longer that ordinary text.',
                ],
                'relationships' => [
                    'address' => [
                        'data' => null,
                    ],
                ],
            ],
        ]);
    }

    public function testSerializeWithRelationship()
    {
        $orderAddress = new OrderAddress();
        $orderAddress->setId(2);
        $orderAddress->setStreet('Street Address 510');

        $order = new Order();
        $order->setId(1);
        $order->setEmail('test@example.com');
        $order->setPhone('+440000000000');
        $order->setAdminComments('Test comments that might be longer that ordinary text.');
        $order->setAddress($orderAddress);

        $serialized = $this->jsonApiSerializer->serialize(
            $order,
            'json',
            Serializer\SerializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame(json_decode($serialized, 1), [
            'data' => [
                'type' => 'order',
                'id' => 1,
                'attributes' => [
                    'email' => 'test@example.com',
                    'phone' => '+440000000000',
                    'admin-comments' => 'Test comments that might be longer that ordinary text.',
                ],
                'relationships' => [
                    'address' => [
                        'data' => [
                            'type' => 'order/address',
                            'id' => 2,
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'order/address',
                    'id' => 2,
                    'attributes' => [
                        'street' => 'Street Address 510',
                    ]
                ]
            ]
        ]);
    }

    protected function setUp()
    {
        $drivers = [
            new YamlDriver(new FileLocator(['Mango\Bundle\JsonApiBundle\Tests\Fixtures' => __DIR__ . '/yml'])),
            new AnnotationDriver(new AnnotationReader())
        ];

        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy('-'));
        $jmsMetadataFactory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $jsonApiChainDriver = new DriverChain($drivers);
        $jsonApiMetadataFactory = new MetadataFactory($jsonApiChainDriver);
        $jsonApiMetadataFactory->setCache(new FileCache(sys_get_temp_dir()));
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

        $this->jsonApiSerializer = new JsonApiSerializer($jmsSerializer, $exclusionStrategy);
    }
}
