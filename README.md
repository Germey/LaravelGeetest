# Laravel Geetest

[![Build Status](https://travis-ci.org/Germey/LaravelGeetest.svg?branch=master)](https://travis-ci.org/Germey/LaravelGeetest)
[![DUB](https://img.shields.io/dub/l/vibe-d.svg?maxAge=2592000?style=plastic)](https://github.com/Germey/LaravelGeetest)
[![Support](https://img.shields.io/badge/support-laravel-orange.svg)](https://laravel.com/)
[![Release](https://img.shields.io/badge/release-v1.0.2-red.svg)](https://github.com/Germey/LaravelGeetest/releases)

Laravel Geetest is a package for Laravel 5 developed by 
[Germey](http://cuiqingcai.com). It provides simple usage for laravel of [Geetest](http://www.geetest.com/). 

Geetest Demo: [Geetest](http://www.geetest.com/exp_normal)

![Geetest Image Demo](http://opencdn.cuiqingcai.com/QQ20160726-0@2x.png)

## Installation

Laravel 5.0.0 or later is required.

To get the latest version of Laravel Markdown, simply require the project using Composer:

```
$ composer require germey/geetest
```

Or you can add following to `require` key in compser.json.

```json
"germey/geetest": "~1.0"
```

then run

```
$ composer update
```

Next, You should need to register the service provider. Open up `config/app.php` and add following into the `providers` key.

```php
Germey\Geetest\GeetestServiceProvider::class
```

And you can register the Geetest Facade in the `aliases` of `config/app.php` if you want.

```php
'Geetest' => Germey\Geetest\Geetest::class
```

## Configuration

To get started, you need to publish all vendor assets using following command.

```
$ php artisan vendor:publish
```

This will create a config file named `config/geetest.php` which you can configure geetest as you like.

It will also generate a views folder `resources/views/vendor/geetest`, here you can configure frontend method of geetest.

## Usage

Firstly, You need to register in [Geetest](http://www.geetest.com/). Creating an app and get `ID` and `KEY`.

Then configure the in your `.env` file because you'd better not make them public.

Add following to `.env`.

```
GEETEST_ID=0f1097bef7xxxxxx9afdeced970c63e4
GEETEST_KEY=c070f0628xxxxxxe68e138b55c56fb3b
```

Next, You need to configure an Ajax validation url route. Default is `/auth/geetest`. 

For example, add this to `routes.php`

```php
Route::get('auth/geetest','Auth\AuthController@getGeetest');
```

Or you can use `Route::controller()`  method to achieve this route.

Next, you can use Trait `Germey\Geetest\CaptchaGeetest` in AuthController which routing '/auth'.

```php
use Germey\Geetest\CaptchaGeetest;
class AuthController extends Controller {
    use CaptchaGeetest;
}
```

Then an Ajax url is configured successfully.

Also you can use this Trait in other Controller but you need to configure  `geetest_url` in `config/geetest.php`.

Finally, You can use in views like following.

```php
{!! Geetest::render() !!}
```

Frequently, It will be used in `form`.

```html
<form action="/" method="post">
    <input name="_token" type="hidden" value="{{ csrf_token() }}">
    <input type="text" name="name">
    {!! Geetest::render() !!}
    <input type="submit" value="submit">
</form>
```

When you click the `submit` button, it will verify the Geetest Code. If you didn't complete the validation, it will alert some text and prevent the form from submitting.

Or you can set other style of Geetest.

```php
{!! Geetest::render('embed') !!}
{!! Geetest::render('popup') !!}
```

Then it will be embed or popup style in the website. Default to `float`.

Also, you can set Geetest Ajax Url by following way.

```
{!! Geetest::setGeetestUrl('/auth/geetest')->render() !!}
```

By `setGeetestUrl` method you can set Geetest Ajax Url. If it is configured, it will override `geetest_url` configured in `config/geetest.php`.

If the validation is completed, the form will be submitted successfully.

## Server Validation

What's the reason that Geetest is safe? If it only has client validation of frontend, can we say it is complete? It also has server validation to ensure that the post request is validate.

First I have to say that you can only use Geetest of Frontend. But you can also do simple things to achieve server validation.

You can use `$this->validate()` method to achieve server validation. Here is an example.

```php
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class BaseController extends Controller 
{
  /**
   * @param Request $request
   */
  public function postValidate(Request $request)
  {
    $result = $this->validate($request, [
      'geetest_challenge' => 'geetest',
    ], [
      'geetest' => Config::get('geetest.server_fail_alert')
    ]);
    if ($result) {
      return 'success';
    }
  }
} 
```

If we use Geetest, the form will post three extra parameters `geetest_challenge` `geetest_validate` `geetest_seccode`. Geetest use these three parameters to achieve server validation.

If you use ORM, we don't need to add these keys to Model, so you should add following in Model.

```php
protected $guarded = ['geetest_challenge', 'geetest_validate', 'geetest_seccode'];
```

You can define alert text by altering `server_fail_alert` in `config/geetest.php`

Also you can use Request to achieve validation.

```php
<?php namespace App\Http\Requests;
use App\Http\Requests\Request;

class ValidationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'geetest_challenge' => 'geetest'
        ];
    }

    /**
     * Get validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'geetest' => 'Validation Failed'
        ];
    }
}

```

We can use it in our Controller by Request parameter.

```php
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Http\Requests\ValidationRequest;

class BaseController extends Controller 
{
  /**
   * @param Request $request
   */
  public function postValidate(ValidationRequest $request)
  {
    // is Validate
  }
} 
```

## Language

Geetest supports different language.

* Simplified Chinese
* Traditional Chinese
* English
* Japanese
* Korean

You can configure it in `config/geetest.php` .

Here are key-values of Languge Configuration.

- zh-cn (Simplified Chinese) 
- zh-tw (Traditional Chinese)
- en (English)
- ja (Japanese)
- ko (Korean)

for example, If you want to use Korean, just change `lang` key to `ko`

```php
'lang' => 'ko'
```

## Contribution

If you find something wrong with this package, you can send an email to `cqc@cuiqingcai.com`

Or just send a pull request to this repository. 

Pull Requests are welcome.

## Author

[Germey](http://cuiqingcai.com) , from Beijing China

## License

Laravel Geetest is licensed under [The MIT License (MIT)](https://github.com/Germey/LaravelGeetest/blob/master/LICENSE).



 

