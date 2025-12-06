<?php

/*
 * This file is part of the Predis package.
 *
 * (c) 2009-2020 Daniele Alessandri
 * (c) 2021-2025 Till Krüss
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MilliCache\Deps\Predis\Protocol\Text\Handler;

use MilliCache\Deps\Predis\Connection\CompositeConnectionInterface;
use MilliCache\Deps\Predis\Response\Error;

/**
 * Handler for the error response type in the standard Redis wire protocol.
 * It translates the payload to a complex response object for Predis.
 *
 * @see http://redis.io/topics/protocol
 */
class ErrorResponse implements ResponseHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(CompositeConnectionInterface $connection, $payload)
    {
        return new Error($payload);
    }
}
