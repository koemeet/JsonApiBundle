<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\EventListener;

use Mango\Bundle\JsonApiBundle\Exception\InvalidDataException;
use Mango\Bundle\JsonApiBundle\Exception\ResourceNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * 
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @return 	array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onException'
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onException(GetResponseForExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        
        $exception = $event->getException();

        if ($exception instanceof InvalidDataException) {
            /* @var $exception InvalidDataException */

            $response = new JsonResponse([
                    'errors' => $this->transformConstraintListToArray($exception->getErrors())
                ], 400
            );
            $event->setResponse($response);
        } elseif ($exception instanceof ResourceNotFoundException) {
            /* @var $exception ResourceNotFoundException */
            $response = new JsonResponse([
                    'errors' => [
                        [
                            'status' => 404,
                            'title' => 'Resource not found',
                            'details' => sprintf('Resource %s#%s not found', $exception->getResourceType(), $exception->getResourceId()),
                            'source' => [
                                'pointer' => '/data/id'
                            ]
                        ]
                    ]
                ], 404
            );
            $event->setResponse($response);
        }

        
        
    }

    /**
     * @param ConstraintViolationListInterface $validationErrors
     * @return array
     */
    private function transformConstraintListToArray(ConstraintViolationListInterface $validationErrors)
    {
        $errors = [];

        foreach ($validationErrors as $validationError) {
            /* @var $validationError ConstraintViolation */
            $error = [
                'title' => $validationError->getMessage(),
                'source' => [
                    'pointer' => $validationError->getPropertyPath()
                ]
            ];

            if (is_scalar($validationError->getInvalidValue())) {
                $error['meta']['value'] = $validationError->getInvalidValue();
            }

            $errors[] = $error;
        }

        return $errors;
    }
}
