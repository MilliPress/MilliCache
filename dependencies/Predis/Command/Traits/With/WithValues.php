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

namespace MilliCache\Predis\Command\Traits\With;

use MilliCache\Predis\Command\Command;

/**
 * @mixin Command
 */
trait WithValues
{
    public function setArguments(array $arguments)
    {
        $withValues = array_pop($arguments);

        if (is_bool($withValues) && $withValues) {
            $arguments[] = 'WITHVALUES';
        } elseif (!is_bool($withValues)) {
            $arguments[] = $withValues;
        }

        parent::setArguments($arguments);
    }
}
