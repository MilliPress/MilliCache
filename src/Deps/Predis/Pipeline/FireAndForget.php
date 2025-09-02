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

namespace MilliCache\Deps\Predis\Pipeline;

use MilliCache\Deps\Predis\Connection\ConnectionInterface;
use SplQueue;

/**
 * Command pipeline that writes commands to the servers but discards responses.
 */
class FireAndForget extends Pipeline
{
    /**
     * {@inheritdoc}
     */
    protected function executePipeline(ConnectionInterface $connection, SplQueue $commands)
    {
        while (!$commands->isEmpty()) {
            $connection->writeRequest($commands->dequeue());
        }

        $connection->disconnect();

        return [];
    }
}
