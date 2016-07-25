<?php namespace Germey\Geetest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class GeetestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'geetest');

        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/vendor/geetest'),
            __DIR__ . '/config.php' => config_path('geetest.php'),
        ]);

        Validator::extend('geetest', function () use ($request) {
            list($geetest_challenge, $geetest_validate, $geetest_seccode) = array_values($request->only('geetest_challenge', 'geetest_validate', 'geetest_seccode'));
            if (session()->get('gtserver') == 1) {
                if (Geetest::successValidate($geetest_challenge, $geetest_validate, $geetest_seccode, session()->get('user_id'))) {
                    return true;
                }
                return false;
            } else {
                if (Geetest::failValidate($geetest_challenge, $geetest_validate, $geetest_seccode, session()->get('user_id'))) {
                    return true;
                }
                return false;
            }
        });

        Blade::extend(function ($value) {
            return preg_replace('/@define(.+)/', '<?php ${1}; ?>', $value);
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('geetest', function () {
            return $this->app->make('Germey\Geetest\GeetestLib');
        });
    }
}
