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
use Mango\Bundle\JsonApiBundle\Serializer\Exclusion\RelationshipExclusionStrategy;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiDeserializationVisitor;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Mango\Bundle\JsonApiBundle\Serializer\Serializer as JsonApiSerializer;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderAddress;
use Mango\Bundle\JsonApiBundle\Tests\TestCase;
use Metadata\MetadataFactory;
use PhpCollection\Map;

class SerializerTest extends TestCase
{
    /** @var JsonApiSerializer */
  protected $jsonApiSerializer;

  /** @var Serializer\Serializer */
  protected $serializer;
    protected $dispatcher;
    protected $objectConstructor;
    protected $factory;
    protected $handlerRegistry;
    protected $serializationVisitors;
    protected $deserializationVisitors;

    public function testDeserializeId()
    {
        $id = 'ORDER-1';
        $data = json_encode(array('data' => array('id' => $id)));

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

        $data = json_encode(array('data' => array('id' => $id, 'attributes' => array('email' => $email))));

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

        $data = json_encode(array('data' => array('id' => $id, 'attributes' => array('admin-comments' => $adminComments))));

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

        $data = json_encode(array(
      'data' => array(
        'id' => $id,
        'relationships' => array(
          'address' => array(
            'data' => array(
              'type' => 'order/address',
              'id' => $addressId,
            ),
          ),
        ),
      ),
      'included' => array(
        array(
          'type' => 'order/address',
          'id' => $addressId,
          'attributes' => array(
            'street' => $addressStreet,
          ),
        ),
      ),
    ));

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

    protected function setUp()
    {
        $this->factory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));

        $this->handlerRegistry = new HandlerRegistry();

        $this->dispatcher = new Serializer\EventDispatcher\EventDispatcher();
        $this->dispatcher->addSubscriber(new Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber());

        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy('-'));

        $this->serializationVisitors = new Map(array(
      'json' => new JsonApiSerializationVisitor($namingStrategy),
    ));
        $this->deserializationVisitors = new Map(array(
      'json' => new JsonApiDeserializationVisitor($namingStrategy),
    ));

        $this->objectConstructor = new Serializer\Construction\UnserializeObjectConstructor();

        $this->serializer = new Serializer\Serializer($this->factory, $this->handlerRegistry, $this->objectConstructor, $this->serializationVisitors, $this->deserializationVisitors, $this->dispatcher);

        $exclusionStrategy = new RelationshipExclusionStrategy($this->factory);

        $this->jsonApiSerializer = new JsonApiSerializer($this->serializer, $exclusionStrategy);
    }
}
