# laravel-flick

[![Latest Stable Version](https://poser.pugx.org/prevailexcel/laravel-flick/v/stable.svg)](https://packagist.org/packages/prevailexcel/laravel-flick)
[![License](https://poser.pugx.org/prevailexcel/laravel-flick/license.svg)](LICENSE.md)
[![PHP Code Style](https://img.shields.io/badge/code_style-PSR--12-brightgreen)](https://www.php-fig.org/psr/psr-12/)
> A Laravel Package for working with Flick Payments seamlessly.

Single connection to access financial data, identity, global accounts and multi-currency payments. Collect payments from individuals or businesses locally and globally, and settle them in multiple currencies, ensuring a cost-effective and hassle-free payment process.

This package also allows you to receive all types of webhooks from [Flick](https://getflick.co) which it verifies and handles the payloads for you. You can start collecting payment in payments in minutes.

    Bank Transfers
    Cards
    Virtual Bank Accounts
    Payouts

## Installation


To get the latest version of Laravel Flick, simply require it

```bash
composer require prevailexcel/laravel-flick
```

Or add the following line to the require block of your `composer.json` file.

```
"prevailexcel/laravel-flick": "1.0.*"
```

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.

Once Laravel Flick is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key. 
> If you use **Laravel >= 5.5** you can skip this step and go to [**`configuration`**](https://github.com/PrevailExcel/laravel-flick#configuration)

```php
'providers' => [
    ...
    PrevailExcel\Flick\FlickServiceProvider::class,
    ...
]
```

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Flick' => PrevailExcel\Flick\Facades\Flick::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="PrevailExcel\Flick\FlickServiceProvider"
```

A configuration-file named `flick.php` with some sensible defaults will be placed in your `config` directory:

```php
<?php

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
```

## General payment flow

Though there are multiple ways to pay an order, most payment gateways expect you to follow the following flow in your checkout process:

### 1. The customer is redirected to the payment provider
After the customer has gone through the checkout process and is ready to pay, the customer must be redirected to the site of the payment provider.

The redirection is accomplished by submitting a form with some hidden fields. The form must send a POST request to the site of the payment provider. The hidden fields minimally specify the amount that must be paid, the order id and a hash.

The hash is calculated using the hidden form fields and a non-public secret. The hash used by the payment provider to verify if the request is valid.


### 2. The customer pays on the site of the payment provider
The customer arrives on the site of the payment provider and gets to choose a payment method. All steps necessary to pay the order are taken care of by the payment provider.

### 3. The customer gets redirected back to your site
After having paid the order the customer is redirected back. In the redirection request to the shop-site some values are returned. The values are usually the order id, a payment result and a hash.

The hash is calculated out of some of the fields returned and a secret non-public value. This hash is used to verify if the request is valid and comes from the payment provider. It is paramount that this hash is thoroughly checked.

## Usage

Open your .env file and add all the necessary keys like so:

```bash
FLICK_SECRET_KEY=sk_****_****************************************
FLICK_ENV=live
```
*If you are using a hosting service like heroku, ensure to add the above details to your configuration variables.*
*Remember to change FLICK_ENV to 'live' and update the keys when you are in production*

#### Next, you have to setup your routes. 
There are 3 routes you should have to get started.
1. To initiate payment
2. To setup callback - Route::flick_callback.
3. To setup webhook and handle the event responses - Route::flick_webhook.

```php
// Laravel 8 & above
Route::post('/pay', [PaymentController::class, 'createPayment'])->name('pay');
Route::flick_callback(PaymentController::class, 'handleGatewayCallback');
Route::flick_webhook(WebhookController::class, 'handleWebhook');
```


#### Let's set our controller
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use PrevailExcel\Flick\Facades\Flick;

class PaymentController extends Controller
{

    /**
     * Redirect the User to Flick Payment Page
     * @return Url
     */
    public function redirectToGateway()
    {
        try{
            return Flick::getLink()->redirectNow();
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['msg'=> $e->getMessage(), 'type'=>'error']);
        }        
    }

    /**
     * Obtain Flick payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = flick()->getPaymentData();

        dd($paymentDetails);
        // Now you have the payment details,
        // you can store the reference ID in your db.
        // you can then redirect or do whatever you want
    }
}
```

```php
/**
 *  In the case where you need to pass the data from your
 *  controller or via your client or app instead of a form
 *  
 */
 $data = [       
        'email' => "example@gmail.com",
        'Phoneno' => "08100000000",
        'amount' => "9000", // in naira
    ];

    // if monolithic, do
    return Flick::getLink($data)->redirectNow();

    // if API, do
    return Flick::getLink($data, true);

```
> You can also seet other details for yourself if the defaults does not work for you.
```php
 $data = [       
        'email' => "example@gmail.com",
        'Phoneno' => "08100000000",
        'amount' => "9000", // in naira

        'transactionId' => "Generate your own ID",
        'currency_collected' => "NGN", // USD, GBP, CAD
        'currency_settled' => 'NGN', // USD, GBP, CAD
    ];
```

### Lets pay with card now.
If you have saved user card in it's encrypted string, you can use it to initate a payment. 

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PrevailExcel\Flick\Facades\Flick;

class PaymentController extends Controller
{  
    /**
     * You collect data from your blade form
     * and this returns the Account details for payment
     */
    public function createPayment()
    {
        try {
            // You can use the global helper flick()->method() or the Facade Flick::method().
           
           
            $card = "eZl0T7elDC3VWefiqYT4RujW7t...";

            return Flick::chargeCard($card);
            
        } catch (\Exception $e) {
            return redirect()->back()->withMessage(['msg' => $e->getMessage(), 'type' => 'error']);
        }
    }
}
```
User will pass a PIN or OTP to complete the payment.

### Handling Webhook
You can listen to the webhook and service the user. Write the heavy operations inside the `handleWebhook()` method.
This package will verify the webhook using the secret hash.

#### In your controller

```php
    public function handleWebhook()
    {
        // verify webhook and get data
        flick()->getWebhookData()->proccessData(function ($data) {
            // Do something with $data
            logger($data);
            $decodedData = json_decode($data, true);
            // Do Something with $decodedData
            
            // If you have heavy operations, dispatch your queued jobs for them here
            // OrderJob::dispatch($decodedData);
        });
        
        // Acknowledge you received the response
        return http_response_code(200);
    }
```

> This package recommends to use a queued job to proccess the webhook data especially if you handle heavy operations like sending mail and more 

##### How does the webhook routing `Route::flick_webhook(Controller::class, 'methodName')` work?

Behind the scenes, by default this will register a POST route `'flick/webhook'` to the controller and method you provide. Because the app that sends webhooks to you has no way of getting a csrf-token, you must add that route to the except array of the VerifyCsrfToken middleware:
```php
protected $except = [
    'flick/webhook',
];
```
In Laravel 11, You can do this inside the bootstrap/app.php file.
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'flick/webhook', 
    ]);
})
```

Add the  `'flick/webhook'` endpoint URL to the three webhook fields on your Flick Dashboard;

    Webhook URL : 127.0.0.1:8000/flick/webhook

![Add Webhook to dashboard](image.png)



 
#### A sample form will look like so:
```blade
<form method="POST" action="{{ route('pay') }}">
    @csrf
    <div class="form-group" style="margin-bottom: 10px;">
        <label for="phone-number">Phone Number</label>
        <input class="form-control" type="tel" name="Phoneno" required />
    </div>
    <div class="form-group" style="margin-bottom: 10px;">
        <label for="email">Email</label>
        <input class="form-control" type="email" name="email" required />
    </div>
    <div class="form-group" style="margin-bottom: 10px;">
        <label for="amount">Amount</label>
        <input class="form-control" type="number" name="amount" required />
    </div>
    <div class="form-submit">
        <button class="btn btn-primary btn-block" type="submit"> Pay </button>
    </div>
</form>
```
When clicking the submit button the customer gets redirected to the Payment page.

So now the customer did some actions there (hopefully he or she paid the order) and now the package will redirect the customer to the Callback URL `Route::flick_callback()`.

We must validate if the redirect to our site is a valid request (we don't want imposters to wrongfully place non-paid order).

In the controller that handles the request coming from the payment provider, we have

`Flick::getPaymentData()` - This function calls the `verifyTransaction()` methods and ensure it is a valid transaction else it throws an exception.


### Some Other fluent methods this package provides are listed here.

#### Collection

```php

/**
 * OTP verification
 *
 * @param string $otp
 * @param string $ref transaction ref or id.
 * @return array
 */
Flick::verifyOtp($otp, $ref);
// Or
flick()->verifyOtp($otp, $ref);

/**
 * PIN verification
 *
 * @param string $pin
 * @param string $ref transaction ref or id.
 * @return array
 */
Flick::verifyPin($pin, $ref);
// Or
flick()->verifyPin($pin, $ref);

/**
 * Get Info About Card
 */
Flick::lookupCard($card_first_six_digits);
```

#### Account

```php

/**
 * Check your balance for the different currencies and categories available. Default is payouts.
 * 
 * @param null|string $category  Can be payouts, walletapi, or collections
 * @param null|string $currency  NGN, USD, GBP, or CAD. Default is NGN
 * @returns array
 */
Flick::checkBalance();

/**
 * Get Flick exchange rate. Either of the parameters must be NGN
 *
 * @param null|string $from  NGN, USD, GBP, or CAD. Default is NGN
 * @param null|string $to  NGN, USD, GBP, or CAD. Default is NGN
 *
 * @return array
 */
Flick::exchangeRate(?string $from = null, ?string $to = null);

/**
 * Generate transfer history statements with custom date ranges
 * @returns array
 */
Flick::transferHistory($data);
```

#### Payout

```php
/**
 * Move funds from your Flick balance to a bank account.
 * @returns array
 */
Flick::transfer($data = null);
```

#### Tools

```php
/**
 * Get all the bank codes for all existing banks in our operating countries.
 * @returns array
 */
Flick::banks();

/**
 * Verify the status of a transaction carried out on your Flick account
 * @returns array
 */
Flick::verifyTransaction(?string $ref = null);
// Or
request()->ref = "transactionId";
flick()->verifyTransaction();

/**
 * Resend webhook for a transaction.
 * @returns array
 */
Flick::resendWebhook(?string $ref = null);
// Or
request()->ref = "transactionId";
flick()->resendWebhook();

/**
 * Verify the status of a transfer carried out from your Flick account 
 * @returns array
 */
Flick::verifyTransfer(?string $ref = null);

/**
 * Verify the owner of a bank account using the bank code and the account uumber 
 * @returns array
 */
Flick::confirmAccount(?string $bank_code = null, ?string $account_number = null);

```

## Todo

* Add webhook Functionality
* Add Comprehensive Tests

## Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

## How can I thank you?

Why not star the github repo? I'd love the attention! Why not share the link for this repository on Twitter or HackerNews? Spread the word!

Thanks!
[Chimeremeze Prevail Ejimadu](https://x.com/EjimaduPrevail)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
