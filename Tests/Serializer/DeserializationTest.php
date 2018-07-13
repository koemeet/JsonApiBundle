<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Serializer;

use JMS\Serializer;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Mango\Bundle\JsonApiBundle\Serializer\Serializer as JsonApiSerializer;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderAddress;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\JsonApiSerializerBuilder;
use Mango\Bundle\JsonApiBundle\Tests\TestCase;

/**
 * Serializer test
 *
 * @property JsonApiSerializer $jsonApiSerializer
 *
 * @author Ruslan Zavacky <ruslan.zavacky@gmail.com>
 */
class DeserializationTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->jsonApiSerializer = JsonApiSerializerBuilder::build();
    }

    /**
     * Test deserialize id
     *
     * @return void
     */
    public function testDeserializeId()
    {
        $id = 'ORDER-1';
        $data = json_encode(['data' => ['id' => $id]]);

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            MangoJsonApiBundle::FORMAT,
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($order->getId(), $id);
    }

    /**
     * Test deserialize attribute
     *
     * @return void
     */
    public function testDeserializeAttribute()
    {
        $id = 'ORDER-1';
        $email = 'hello@example.com';

        $data = json_encode(['data' => ['id' => $id, 'attributes' => ['email' => $email]]]);

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            MangoJsonApiBundle::FORMAT,
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($order->getId(), $id);
        $this->assertSame($order->getEmail(), $email);
    }

    /**
     * Test deserialize dasherized attributes
     *
     * @return void
     */
    public function testDeserializeDasherizedAttributes()
    {
        $id = 'ORDER-1';
        $adminComments = 'Admin comment';

        $data = json_encode(['data' => ['id' => $id, 'attributes' => ['admin-comments' => $adminComments]]]);

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            MangoJsonApiBundle::FORMAT,
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($order->getId(), $id);
        $this->assertSame($order->getAdminComments(), $adminComments);
    }

    /**
     * Test deserialize attributes with serialized property name overide
     *
     * @return void
     */
    public function testDeserializeAttributesWithSerializedNameOverride()
    {
        $id = 'ORDER-1';
        $orderDate = '2018-01-01T00:00:00+0300';

        $data = json_encode(
            [
                'data' => [
                    'id'         => $id,
                    'attributes' => [
                        'date' => $orderDate
                    ]
                ]
            ]
        );

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            MangoJsonApiBundle::FORMAT,
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($order->getId(), $id);
        $this->assertEquals($order->getOrderDate(), new \DateTime($orderDate));
    }

    /**
     * Test deserialize single relationship
     *
     * @return void
     */
    public function testDeserializeSingleRelationship()
    {
        $id = 'ORDER-1';
        $addressId = 'ORDER-ADDRESS-1';
        $addressStreet = 'Address street';

        $data = json_encode(
            [
                'data'     => [
                    'id'            => $id,
                    'relationships' => [
                        'address' => [
                            'data' => [
                                'type' => 'order/address',
                                'id'   => $addressId,
                            ],
                        ],
                    ],
                ],
                'included' => [
                    [
                        'type'       => 'order/address',
                        'id'         => $addressId,
                        'attributes' => [
                            'street' => $addressStreet,
                        ],
                    ],
                ],
            ]
        );

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            MangoJsonApiBundle::FORMAT,
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );

        $this->assertSame($id, $order->getId());
        $this->assertSame(true, $order->getAddress() instanceof OrderAddress);
        $this->assertSame($addressStreet, $order->getAddress()->getStreet());
    }

    /**
     * Test deserialize ArrayCollection relationship
     *
     * @return void
     */
    public function testDeserializeArrayCollectionRelationship()
    {
        $id = 'ORDER-1';
        $firstCouponId = '11';
        $secondCouponId = '22';

        $data = json_encode(
            [
                'data'     => [
                    'id'            => $id,
                    'relationships' => [
                        'gift-coupons' => [
                            'data' => [
                                (object)[
                                    'type'       => 'order/coupon',
                                    'id'         => $firstCouponId
                                ],
                                (object)[
                                    'type'       => 'order/coupon',
                                    'id'         => $secondCouponId
                                ]
                            ],
                        ],
                    ],
                ]
            ]
        );

        /** @var Order $order */
        $order = $this->jsonApiSerializer->deserialize(
            $data,
            Order::class,
            MangoJsonApiBundle::FORMAT,
            Serializer\DeserializationContext::create()->setSerializeNull(true)
        );
        $this->assertSame($id, $order->getId());

        $giftCoupons = $order->getGiftCoupons();
        $this->assertSame($firstCouponId, $giftCoupons[0]->getId());
        $this->assertSame($secondCouponId, $giftCoupons[1]->getId());
    }
}
