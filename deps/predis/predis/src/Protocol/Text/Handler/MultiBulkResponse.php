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

use MilliCache\Deps\Predis\CommunicationException;
use MilliCache\Deps\Predis\Connection\CompositeConnectionInterface;
use MilliCache\Deps\Predis\Protocol\ProtocolException;

/**
 * Handler for the multibulk response type in the standard Redis wire protocol.
 * It returns multibulk responses as PHP arrays.
 *
 * @see http://redis.io/topics/protocol
 */
class MultiBulkResponse implements ResponseHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(CompositeConnectionInterface $connection, $payload)
    {
        $length = (int) $payload;

        if ("$length" !== $payload) {
            CommunicationException::handle(new ProtocolException(
                $connection, "Cannot parse '$payload' as a valid length of a multi-bulk response [{$connection->getParameters()}]"
            ));
        }

        if ($length === -1) {
            return;
        }

        $list = [];

        if ($length > 0) {
            $handlersCache = [];
            $reader = $connection->getProtocol()->getResponseReader();

            for ($i = 0; $i < $length; ++$i) {
                $header = $connection->readLine();
                $prefix = $header[0];

                if (isset($handlersCache[$prefix])) {
                    $handler = $handlersCache[$prefix];
                } else {
                    $handler = $reader->getHandler($prefix);
                    $handlersCache[$prefix] = $handler;
                }

                $list[$i] = $handler->handle($connection, substr($header, 1));
            }
        }

        return $list;
    }
}
