<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Load dynamic settings from DB into config (if table exists)
        if (Schema::hasTable('settings')) {
            $appName  = \App\Models\Setting::get('app_name');
            $currency = \App\Models\Setting::get('currency_symbol');
            $dateFmt  = \App\Models\Setting::get('date_format');

            if ($appName)  config(['app.name' => $appName]);
            if ($currency) config(['app.currency_symbol' => $currency]);
            if ($dateFmt)  config(['app.date_format' => $dateFmt]);
        }

        // @money($amount) => "৳ 12,345.00"
        Blade::directive('money', function ($expression) {
            return "<?php echo config('app.currency_symbol', '৳') . ' ' . number_format((float)($expression), 2); ?>";
        });

        // @moneyplain($amount) => "12,345.00" (no symbol)
        Blade::directive('moneyplain', function ($expression) {
            return "<?php echo number_format((float)($expression), 2); ?>";
        });

        // @date($date) formats a date per config
        Blade::directive('bmsdate', function ($expression) {
            return "<?php echo ($expression) ? \\Carbon\\Carbon::parse($expression)->format(config('app.date_format', 'd M Y')) : '—'; ?>";
        });
    }
}

