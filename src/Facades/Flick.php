<?php

namespace PrevailExcel\Flick\Facades;

use Illuminate\Support\Facades\Facade;

/*
 * This file is part of the Laravel Flick package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Flick extends Facade
{
    /**
     * Get the registered name of the component
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-flick';
    }
}
