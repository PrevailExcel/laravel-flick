<?php

/*
 * This file is part of the Laravel Flick package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /**
     * Public Key From FLICK Dashboard
     *
     */
    'secretKey' => getenv('FLICK_SECRET_KEY'),

    /**
     * You enviroment can either be live or stage.
     * Make sure to add the appropriate API key after changing the enviroment in .env
     *
     */
    'env' => env('FLICK_ENV', 'live'), // OR "sandbox"

    /**
     * FLICK Base URL
     *
     */
    'baseUrl' => env('FLICK_LIVE_URL', "https://flickopenapi.co"),

];
