<?php

namespace PrevailExcel\Flick;

/*
 * This file is part of the Laravel Flick package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait Account
{
    /**
     * Check your balance for the different currencies and categories available
     *
     * @param null|string $category  Can be payouts, walletapi, or collections
     * @param null|string $currency  NGN, USD, GBP, or CAD. Default is NGN
     *
     * @return array
     */
    public function checkBalance(?string $category = "payouts", ?string $currency = null): array
    {

        if (!$currency)
            $currency = request()->currency ?? "NGN";

        if ($category != "payouts" || $category != "walletapi" || $category != "collections")
            $category = "payouts";

        return $this->setHttpResponse("/merchant/balances?category=$category&currency=$currency", 'GET')->getResponse();
    }

    /**
     * Get flick exchange rate Either of the parameters must be NGN
     *
     * @param null|string $from  NGN, USD, GBP, or CAD. Default is NGN
     * @param null|string $to  NGN, USD, GBP, or CAD. Default is NGN
     *
     * @return array
     */
    public function exchangeRate(?string $from = null, ?string $to = null): array
    {

        if (!$from)
            $from = request()->from ?? "NGN";
        if (!$to)
            $to = request()->$to ?? "NGN";

        return $this->setHttpResponse("/merchant/exchange-rate?from_currency=$from&to_currency=$to", 'GET')->getResponse();
    }

    /**
     * Generate transfer history statements with custom date ranges and limits
     *
     * @param array $data
     * @return array
     */
    public function transferHistory($data = null): array
    {

        if ($data == null) {
            $data = array_filter([
                "category" => request()->category ?? "collections",
                "currency" => request()->currency ?? "NGN",
                'limit' => request()->limit ?? 50,
                "date_begin" => request()->date_begin,
                "date_end" => request()->date_end
            ]);
            return $this->setHttpResponse('/merchant/transactions', 'POST', $data)->getResponse();
        }
    }
}
