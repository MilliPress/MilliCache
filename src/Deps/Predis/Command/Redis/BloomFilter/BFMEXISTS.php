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

namespace MilliCache\Deps\Predis\Command\Redis\BloomFilter;

use MilliCache\Deps\Predis\Command\Command as RedisCommand;

/**
 * @see https://redis.io/commands/bf.mexists/
 *
 * Determines if one or more items may exist in the filter or not.
 */
class BFMEXISTS extends RedisCommand
{
    public function getId()
    {
        return 'BF.MEXISTS';
    }
}
