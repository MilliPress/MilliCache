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

namespace MilliCache\Predis\Command\Redis\Search;

use MilliCache\Predis\Command\Command as RedisCommand;

class FTDROPINDEX extends RedisCommand
{
    public function getId()
    {
        return 'FT.DROPINDEX';
    }

    public function setArguments(array $arguments)
    {
        [$index] = $arguments;
        $commandArguments = [];

        if (!empty($arguments[1])) {
            $commandArguments = $arguments[1]->toArray();
        }

        parent::setArguments(array_merge(
            [$index],
            $commandArguments
        ));
    }
}
