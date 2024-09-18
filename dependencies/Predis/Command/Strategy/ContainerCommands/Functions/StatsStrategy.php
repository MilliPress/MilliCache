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

namespace MilliCache\Predis\Command\Strategy\ContainerCommands\Functions;

use MilliCache\Predis\Command\Strategy\SubcommandStrategyInterface;

class StatsStrategy implements SubcommandStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function processArguments(array $arguments): array
    {
        return $arguments;
    }
}
