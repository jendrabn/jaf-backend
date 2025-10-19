<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('Rp', function ($money) {
            return "<?php echo 'Rp '. number_format($money,0,',','.'); ?>";
        });

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            if (request()->is('api/*')) {
                return config('shop.front_url')."/auth/reset-password?email={$user->email}&token={$token}";
            } else {
                return route('auth.reset_password', ['token' => $token, 'email' => $user->email]);
            }
        });
    }
}
