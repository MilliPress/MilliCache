<?php

/*
 * This file is part of the MilliCache\Deps\Predis package.
 *
 * (c) 2009-2020 Daniele Alessandri
 * (c) 2021-2025 Till Krüss
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MilliCache\Deps\Predis\Response;

use MilliCache\Deps\Predis\PredisException;

/**
 * Exception class that identifies server-side Redis errors.
 */
class ServerException extends PredisException implements ErrorInterface
{
    /**
     * Gets the type of the error returned by Redis.
     *
     * @return string
     */
    public function getErrorType()
    {
        [$errorType] = explode(' ', $this->getMessage(), 2);

        return $errorType;
    }

    /**
     * Converts the exception to an instance of MilliCache\Deps\Predis\Response\Error.
     *
     * @return Error
     */
    public function toErrorResponse()
    {
        return new Error($this->getMessage());
    }
}
