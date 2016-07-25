# Laravel Geetest

Laravel Geetest is a package for Laravel 5 developed by 
[Germey](http://cuiqingcai.com). It provides simple usage for laravel of [Geetest](http://www.geetest.com/). 

## Installation

Laravel 5.0.0 or later is required.

To get the latest version of Laravel Markdown, simply require the project using Composer:

```
$ composer require germey/geetest
```

Or you can add following to `require` key in compser.json.

```
"germey/geetest": "dev-master"
```

then run

```
$ composer update
```

Next, You should need to register the service provider. Open up `config/app.php` and add following into the `providers` key.

```
Germey\Geetest\GeetestServiceProvider::class 
```

And you can register the Geetest Facade in the `aliases` of `config/app.php` if you want.

```
'Geetest' => Germey\Geetest\Geetest::class,
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

Next, You need to configure an Ajax validation url. Default is `/auth/geetest`. So you can use Trait `Germey\Geetest\CaptchaGeetest` in AuthController which routing '/auth'.

```php
use Germey\Geetest\CaptchaGeetest;
class AuthController extends Controller {
    use CaptchaGeetest;
}
```

Then an Ajax url is configured successfully.

Also you can use this Trait in other Controller but you need to configure  `geetest_url` in `config/geetest.php`.

Finally, You can use in views like following.

```
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

```
{!! Geetest::render('embed') !!}
{!! Geetest::render('popup') !!}
``` 

Then it will be embed or popup style in the website. Default to `float`.

If the validation is completed, the form will be submitted successfully.








