<?php

/*
 * This file is part of the MilliCache\Predis package.
 *
 * (c) 2009-2020 Daniele Alessandri
 * (c) 2021-2023 Till Krüss
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MilliCache\Predis\Protocol\Text\Handler;

use MilliCache\Predis\CommunicationException;
use MilliCache\Predis\Connection\CompositeConnectionInterface;
use MilliCache\Predis\Protocol\ProtocolException;

/**
 * Handler for the integer response type in the standard Redis wire protocol.
 * It translates the payload an integer or NULL.
 *
 * @see http://redis.io/topics/protocol
 */
class IntegerResponse implements ResponseHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(CompositeConnectionInterface $connection, $payload)
    {
        if (is_numeric($payload)) {
            $integer = (int) $payload;

            return $integer == $payload ? $integer : $payload;
        }

        if ($payload !== 'nil') {
            CommunicationException::handle(new ProtocolException(
                $connection, "Cannot parse '$payload' as a valid numeric response [{$connection->getParameters()}]"
            ));
        }

        return;
    }
}
