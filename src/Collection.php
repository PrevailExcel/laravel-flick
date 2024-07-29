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

trait Collection
{

    /**
     * Fluent method to redirect to Flick Payment Page
     */
    public function redirectNow()
    {
        return redirect($this->url);
    }

    /**
     * Make payment via Card Charging
     *
     * @param string $card encrypted card string
     * @param null|string $ref transaction ref or id. Leave null to be generated
     *
     * @return array
     */
    public function chargeCard($card, ?string $ref = null): array
    {
        if (!$ref)
            $ref = request()->ref ?? Random::generate();

        $data = [
            "transactionId" => $ref,
            "cardDetails" => $card
        ];

        return $this->setHttpResponse('/collection/charge', 'POST', array_filter($data))->getResponse();
    }

    /**
     * Pin verification
     *
     * @param string $pin
     * @param null|string $ref transaction ref or id.
     *
     * @return array
     */
    public function verifyPin($pin, $ref): array
    {
        $data = [
            "transactionId" => $ref,
            "pin" => $pin
        ];

        return $this->setHttpResponse('/collection/verify-pin', 'POST', array_filter($data))->getResponse();
    }


    /**
     * OTP verification
     *
     * @param string $otp
     * @param null|string $ref transaction ref or id.
     *
     * @return array
     */
    public function verifyOtp($otp, $ref): array
    {
        $data = [
            "transactionId" => $ref,
            "otp" => $otp
        ];

        return $this->setHttpResponse('/collection/verify-otp', 'POST', array_filter($data))->getResponse();
    }

    /**
     * Get Info About Card
     *
     * @param string $card_first_six_digits First 6 digits of your card
     *
     * @return array
     */
    public function lookupCard($card_first_six_digits): array
    {
        $data = [
            "cardBin" => $card_first_six_digits
        ];

        return $this->setHttpResponse('/collection/bin-lookup', 'POST', array_filter($data))->getResponse();
    }
}
