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

trait Tools
{

    /**
     * Get all the bank codes for all existing banks in our operating countries.
     *
     * @param null|string $amount
     * @return array
     */
    public function banks(): array
    {
        return $this->setHttpResponse('/merchant/banks', 'GET')->getResponse();
    }

    /**
     * Verify the status of a transaction carried out on your Flick account
     *
     * @param null|string $ref
     * @return array
     */
    public function verifyTransaction(?string $ref = null): array
    {
        if (!$ref)
            $ref = request()->ref;

        $data = array_filter([
            "transactionId" => $ref
        ]);

        return $this->setHttpResponse('/transaction/status', 'POST', $data)->getResponse();
    }

    /**
     * Resend webhook for a transaction.
     *
     * @param null|string $ref
     * @return array
     */
    public function resendWebhook(?string $ref = null): array
    {
        if (!$ref)
            $ref = request()->ref;

        $data = array_filter([
            "transactionId" => $ref
        ]);

        return $this->setHttpResponse('/merchant/resend-webhook', 'POST', $data)->getResponse();
    }

    /**
     * Verify the status of a transfer carried out from your Flick account
     *
     * @param null|string $id
     * @return array
     */
    public function verifyTransfer(?string $id = null): array
    {
        if (!$id)
            $id = request()->id;

        return $this->setHttpResponse("/transfer/verify/$id", 'GET')->getResponse();
    }

    /**
     * Verify the owner of a bank account using the bank code and the account uumber
     *
     * @param null|string $bank_code  Bank code from getbanks endpoint
     * @param null|string $account_number Users account number
     * @return array
     */
    public function confirmAccount(?string $bank_code = null, ?string $account_number = null): array
    {

        if (!$bank_code)
            $bank_code = request()->bank_code ?? null;
        if (!$account_number)
            $account_number = request()->$account_number ?? null;

        $data = array_filter([
            "bank_code" => $bank_code,
            "account_number" => $account_number
        ]);
        return $this->setHttpResponse('/merchant/name-enquiry', 'POST', $data)->getResponse();
    }
}
