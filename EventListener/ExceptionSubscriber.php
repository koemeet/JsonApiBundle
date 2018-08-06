<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\EventListener;

use JMS\Serializer\SerializerInterface;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber
 *
 * @author Sergey Chernecov <sergey.chernecov@gmail.com>
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Serializer
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Enabled
     *
     * @var bool
     */
    private $enabled;

    /**
     * Exception subscriber constructor
     *
     * @param SerializerInterface $serializer
     * @param bool                $enabled
     */
    public function __construct(SerializerInterface $serializer, $enabled = false)
    {
        $this->serializer = $serializer;
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => array('onKernelException', -128),
        ];
    }

    /**
     * On kernel exception
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $exception = $event->getException();

        $this->logger->warning(
            'Exception has been thrown.',
            [
                'exception_code'    => $exception->getCode(),
                'exception_message' => $exception->getMessage(),
                'exception_file'    => $exception->getFile(),
                'exception_trace'   => $exception->getTraceAsString(),
                'exception_line'    => $exception->getLine()
            ]
        );

        $content = $this->serializer->serialize(
            $exception,
            MangoJsonApiBundle::FORMAT
        );

        $event->setResponse(new JsonApiResponse($content, JsonApiResponse::HTTP_BAD_REQUEST));
        $event->stopPropagation();
    }
}
