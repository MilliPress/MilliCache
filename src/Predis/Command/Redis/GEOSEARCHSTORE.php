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

namespace MilliCache\Predis\Command\Redis;

use MilliCache\Predis\Command\Command as RedisCommand;
use MilliCache\Predis\Command\Traits\By\GeoBy;
use MilliCache\Predis\Command\Traits\Count;
use MilliCache\Predis\Command\Traits\From\GeoFrom;
use MilliCache\Predis\Command\Traits\Sorting;
use MilliCache\Predis\Command\Traits\Storedist;

/**
 * @see https://redis.io/commands/geosearchstore/
 *
 * This command is like GEOSEARCH, but stores the result in destination key.
 */
class GEOSEARCHSTORE extends RedisCommand
{
    use GeoFrom {
        GeoFrom::setArguments as setFrom;
    }
    use GeoBy {
        GeoBy::setArguments as setBy;
    }
    use Sorting {
        Sorting::setArguments as setSorting;
    }
    use Count {
        Count::setArguments as setCount;
    }
    use Storedist {
        Storedist::setArguments as setStoreDist;
    }

    protected static $sortArgumentPositionOffset = 4;
    protected static $countArgumentPositionOffset = 5;
    protected static $storeDistArgumentPositionOffset = 7;

    public function getId()
    {
        return 'GEOSEARCHSTORE';
    }

    public function setArguments(array $arguments)
    {
        $this->setStoreDist($arguments);
        $arguments = $this->getArguments();

        $this->setCount($arguments, $arguments[6] ?? false);
        $arguments = $this->getArguments();

        $this->setSorting($arguments);
        $arguments = $this->getArguments();

        $this->setFrom($arguments);
        $arguments = $this->getArguments();

        $this->setBy($arguments);
        $this->filterArguments();
    }
}
