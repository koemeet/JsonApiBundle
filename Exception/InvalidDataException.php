<?php

/*
 * This file is part of the Mango package.
 *
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * InvalidDataException
 */
class InvalidDataException extends BadRequestHttpException
{
    /**
     * @var ConstraintViolationListInterface
     */
    protected $validationErrors;
    
    public function __construct(ConstraintViolationListInterface $validationErrors, $message = 'Data Validation Failed', \Exception $previous = null, $code = 0)
    {
        $this->validationErrors = $validationErrors;

        parent::__construct($message, $previous, $code);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getErrors()
    {
        return $this->validationErrors;
    }
}
