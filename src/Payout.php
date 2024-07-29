<?php

namespace PrevailExcel\Flick;

use Nette\Utils\Random;

/*
 * This file is part of the Laravel Flick package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait Payout
{

    /**
     * Move funds from your Flick balance to a bank account.
     *
     * @param array $data
     * @return array
     */
    public function transfer($data = null): array
    {

        $def = [
            "eference" => Random::generate(),
            "currency" => request()->currency ?? "NGN",
            "debit_currency" => request()->debit_currency ?? "NGN",
        ];

        if ($data == null) {
            $data = [
                'bank_name' => request()->bank_name,
                'bank_code' => request()->bank_code,
                'beneficiary_name' => request()->beneficiary_name,
                'account_number' => request()->account_number,
                'amount' => request()->amount,
                'narration' => request()->narration,
                'email' => request()->email,
                'mobile_number' => request()->mobile_number,
                'meta' => request()->meta
            ];
        }
        $data = array_merge($def, $data);

        return $this->setHttpResponse('/transfer/payout', 'POST', array_filter($data))->getResponse();
    }
}
