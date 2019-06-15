<?php

namespace App\Providers;

use App\Observers\{
    AvatarObserver,
    NotificationObserver,
    UserObserver
};
use App\{
    Avatar,
    Notification,
    User
};
use Auth, Horizon, URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        URL::forceScheme('https');
        Schema::defaultStringLength(191);

        Avatar::observe(AvatarObserver::class);
        Notification::observe(NotificationObserver::class);
        User::observe(UserObserver::class);

        Horizon::auth(function ($request) {
            return Auth::check() && $request->user()->is_admin;
        });

        Blade::directive('prettyNumber', function ($expression) {
            $num = $expression;
            $abbrevs = [12 => 'T', 9 => 'B', 6 => 'M', 3 => 'K', 0 => ''];
            foreach ($abbrevs as $exponent => $abbrev) {
                if ($expression >= pow(10, $exponent)) {
                    $display_num = $expression / pow(10, $exponent);
                    $num = number_format($display_num, 0).$abbrev;

                    return "<?php echo '$num'; ?>";
                }
            }

            return "<?php echo $num; ?>";
        });

        Blade::directive('prettySize', function ($expression) {
            $size = \App\Util\Lexer\PrettyNumber::size($expression);
            return "<?php echo '$size'; ?>";
        });

        Blade::directive('maxFileSize', function () {
            $value = config('pixelfed.max_photo_size');

            return \App\Util\Lexer\PrettyNumber::size($value, true);
        });

        // Force Carbon to use app locale
        \Carbon\Carbon::setLocale('de_DE.utf8');
        setlocale(LC_TIME, 'de_DE.utf8');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
