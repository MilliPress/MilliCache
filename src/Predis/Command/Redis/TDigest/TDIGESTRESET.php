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

namespace MilliCache\Predis\Command\Redis\TDigest;

use MilliCache\Predis\Command\Command as RedisCommand;

/**
 * @see https://redis.io/commands/tdigest.reset/
 *
 * Resets a t-digest sketch: empty the sketch and re-initializes it.
 */
class TDIGESTRESET extends RedisCommand
{
    public function getId()
    {
        return 'TDIGEST.RESET';
    }
}
