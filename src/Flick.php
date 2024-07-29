<?php

namespace PrevailExcel\Flick;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Nette\Utils\Random;

/*
 * This file is part of the Laravel Flick package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Flick
{
    use Collection, Account, Payout, Tools;


    /**
     * Issue secret Key from your Flick Dashboard
     * @var string
     */
    protected $secretKey;

    /**
     * Instance of Client
     * @var Client
     */
    protected $client;

    /**
     *  Response from requests made to Flick
     * @var mixed
     */
    protected $response;

    /**
     * Flick API base Url
     * @var string
     */
    protected $baseUrl;

    /**
     * Generated url for user
     * @var string
     */
    protected $url;

    /**
     * Flick API Enviroment
     * @var string
     */
    protected $env;

    /**
     * Secret Hash set up on Flick Dashboard
     * @var string
     */
    protected $hash;

    /**
     * Verified Data from Webhook
     */
    protected $webhookData;

    /**
     * Your callback Url. You can set this in your .env or you can add
     * it to the $data in the methods that require that option.
     * @var string
     */
    protected $callbackUrl;

    public function __construct()
    {
        $this->setUp();
        $this->setRequestOptions();
    }

    /**
     * Set properties from Flick config file
     */
    private function setUp()
    {
        $this->secretKey = Config::get('flick.secretKey');
        $this->env = Config::get('flick.env');
        $this->hash = Config::get('flick.hash');
        $this->baseUrl = Config::get('flick.baseUrl');
    }

    /**
     * Set options for making the Client request
     */
    private function setRequestOptions()
    {
        $headers = [
            'authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json'
        ];

        $this->client = new Client(
            [
                'base_uri' => $this->baseUrl,
                'headers' => $headers
            ]
        );
    }

    /**
     * @param string $relativeUrl
     * @param string $method
     * @param array $body
     * @return Flick
     * @throws IsNullException
     */
    private function setHttpResponse($relativeUrl, $method, $body = [])
    {
        if (is_null($method)) {
            throw new IsNullException("Empty method not allowed");
        }

        $this->response = $this->client->{strtolower($method)}(
            $this->baseUrl . $relativeUrl,
            ["body" => json_encode($body)]
        );

        return $this;
    }


    /**
     * Get the whole response from a get operation
     * @return array
     */
    private function getResponse()
    {
        return json_decode($this->response->getBody(), true);
    }

    /**
     * Verify webhook data
     *
     * @return Flick
     * @throws IsNullException
     */
    public function getWebhookData()
    {
        if (request()->header('Verification-Hash'))
            $verified = $this->hash == request()->header('Verification-Hash');

        if ($verified) {
            $data = json_decode(request()->getContent(), true);
            $this->webhookData = json_encode($data);

            return $this;
        } else
            throw IsNullException::make();
    }

    /**
     * Handle webhook data
     * @return array
     */
    public function proccessData(callable|Closure $callback)
    {
        call_user_func($callback, $this->webhookData);
        return true;
    }

    /**
     * Creates a unique link that directs customers to the checkout page for completing their payments.
     *
     * @param array|null $data
     * @param bool $show
     * @return Flick|array
     */
    public function getLink($data = null, bool $show = false)
    {
        $def = [
            'currency_collected' => "NGN",
            'transactionId' => Random::generate(),
            'currency_settled' => 'NGN',
            'redirectUrl' => route('flick.lara.callback'),
            'webhookUrl' => route('flick.lara.webhook'),
        ];

        if ($data == null) {
            $data = [
                'email' => request()->email,
                'Phoneno' => request()->phone,
                'amount' => request()->amount,
            ];
        }

        $data["amount"] *= 100;
        $data["amount"] = (string) $data["amount"];
        $data = array_merge($def, $data);

        $response = $this->setHttpResponse('/collection/create-charge', 'POST', $data)->getResponse();
        if ($response["status"] == "success") {
            $this->url = $response["data"]["url"];

            // If $show is true, return the response, else return the class
            if ($show)
                return $response;
            return $this;
        }
        return $response;
    }


    /**
     * Get payment data
     * @return array
     */
    public function getPaymentData()
    {
        $ref = request()->transactionId;
        $paymentdata = $this->verifyTransaction($ref);
        return $paymentdata;
    }
}
