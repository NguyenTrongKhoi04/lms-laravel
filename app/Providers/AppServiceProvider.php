<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // // Đăng ký một service vào container
        // $this->app->singleton('SomeService', function ($app) {
        //     return new \App\Services\SomeService();
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Khởi tạo một directive cho Blade
        /*
        Blade::directive('datetime', function ($expression) {
            return "<?= echo ($expression)->format('m/d/Y H:i'); ?>";
});
*/

        if (\Schema::hasTable('smtp_settings')) {
            $smtpsetting = SmtpSetting::first();

            if ($smtpsetting) {
                $data = [
                    'driver' => $smtpsetting->mailer,
                    'host' => $smtpsetting->host,
                    'port' => $smtpsetting->port,
                    'username' => $smtpsetting->username,
                    'password' => $smtpsetting->password,
                    'encryption' => $smtpsetting->encryption,
                    'from' => [
                        'address' => $smtpsetting->from_address,
                        'name' => 'Easycourselms'
                    ]
                ];

                Config::set('mail', $data);
            }
        };
    }
}
