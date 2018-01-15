<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Serializer;

use JMS\Serializer;
use Mango\Bundle\JsonApiBundle\Serializer\Serializer as JsonApiSerializer;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderAddress;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderItem;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderPaymentCard;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderPaymentCash;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\JsonApiSerializerBuilder;
use Mango\Bundle\JsonApiBundle\Tests\TestCase;

/**
 * Serializer test
 *
 * @property JsonApiSerializer $jsonApiSerializer
 *
 * @author Ruslan Zavacky <ruslan.zavacky@gmail.com>
 */
class SerializerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->jsonApiSerializer = JsonApiSerializerBuilder::build();
    }

    /**
     * Test simple serialize
     *
     * @return void
     */
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
                    'payment' => [
                        'data' => null,
                    ],
                    'items' => [
                        'data' => [],
                    ]
                ],
            ],
        ]);
    }

    /**
     * Test serialize with relationship
     *
     * @return void
     */
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
                    'payment' => [
                        'data' => null,
                    ],
                    'items' => [
                        'data' => [],
                    ]
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

    /**
     * Test serialize with one to many relationship
     *
     * @return void
     */
    public function testSerializeWithOneToManyRelationship()
    {
        $orderAddress = new OrderAddress();
        $orderAddress->setId(2);
        $orderAddress->setStreet('Street Address 510');

        $orderItem1 = new OrderItem();
        $orderItem1->setId(1);
        $orderItem1->setTitle('Item 1');

        $orderItem2 = new OrderItem();
        $orderItem2->setId(2);
        $orderItem2->setTitle('Item 2');

        $order = new Order();
        $order->setId(1);
        $order->setEmail('test@example.com');
        $order->setPhone('+440000000000');
        $order->setAdminComments('Test comments that might be longer that ordinary text.');
        $order->setAddress($orderAddress);
        $order->setItems([$orderItem1, $orderItem2]);

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
                    'payment' => [
                        'data' => null,
                    ],
                    'items' => [
                        'data' => [
                            [
                                'type' => 'order/item',
                                'id' => 1,
                            ],
                            [
                                'type' => 'order/item',
                                'id' => 2,
                            ],
                        ]
                    ]
                ],
            ],
            'included' => [
                [
                    'type' => 'order/address',
                    'id' => 2,
                    'attributes' => [
                        'street' => 'Street Address 510',
                    ]
                ],
                [
                    'type' => 'order/item',
                    'id' => 1,
                    'attributes' => [
                        'title' => 'Item 1',
                    ]
                ],
                [
                    'type' => 'order/item',
                    'id' => 2,
                    'attributes' => [
                        'title' => 'Item 2',
                    ]
                ]
            ]
        ]);
    }

    /**
     * Test serialize with discriminator map relationship
     *
     * @return void
     */
    public function testSerializeWithDiscriminatorMapRelationship()
    {
        $this->markTestSkipped('WIP');

        $cardPayment = new OrderPaymentCard();
        $cardPayment->setId(1);
        $cardPayment->setAmount(10.00);

        $cashPayment = new OrderPaymentCash();
        $cashPayment->setId(2);
        $cashPayment->setAmount(20.00);

        $order = new Order();
        $order->setId(1);
        $order->setPayment($cardPayment);

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
                    'email' => null,
                    'phone' => null,
                    'admin-comments' => null,
                ],
                'relationships' => [
                    'address' => [
                        'data' => null,
                    ],
                    'payment' => [
                        'data' => [
                            'type' => 'order/payment-card',
                            'id' => 1,
                        ],
                    ],
                    'items' => [
                        'data' => [],
                    ]
                ],
            ],
            'included' => [
                [
                    'type' => 'order/payment-card',
                    'id' => 1,
                    'attributes' => [
                        'amount' => 10,
                        'type' => 'card',
                    ],
                ]
            ]
        ]);

        $order = new Order();
        $order->setId(2);
        $order->setPayment($cashPayment);

        $serialized = $this->jsonApiSerializer->serialize(
            $order,
            'json',
            Serializer\SerializationContext::create()->setSerializeNull(true)
        );

        // TODO: here is a bug. When serialized, relationships merge in include property
        // relationship from the previous serialize, some how leak into the include
        // of the 2nd ->serialize call.

        $this->assertSame(json_decode($serialized, 1), [
            'data' => [
                'type' => 'order',
                'id' => 2,
                'attributes' => [
                    'email' => null,
                    'phone' => null,
                    'admin-comments' => null,
                ],
                'relationships' => [
                    'address' => [
                        'data' => null,
                    ],
                    'payment' => [
                        'data' => [
                            'type' => 'order/payment-cash',
                            'id' => 2,
                        ],
                    ],
                    'items' => [
                        'data' => [],
                    ]
                ],
            ],
            'included' => [
                [
                    'type' => 'order/payment-cash',
                    'id' => 2,
                    'attributes' => [
                        'amount' => 20,
                        'type' => 'cash',
                    ],
                ]
            ]
        ]);
    }
}
