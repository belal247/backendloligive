<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Subscription;
use App\Models\BillingLog;

// Optional command included by Laravel
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// 🔁 Monthly billing task — reset usage and create invoice
Schedule::daily()->call(function () {
    $today = now()->toDateString();

    Subscription::whereDate('renewal_date', $today)->chunk(50, function ($subscriptions) {
        foreach ($subscriptions as $sub) {
            $limit = $sub->package->monthly_limit ?? 0;
            $overage = max(0, $sub->api_calls_used - $limit);
            $rate = $sub->package->overage_rate ?? 0;

            BillingLog::create([
                'user_id'        => $sub->user_id,
                'merchant_id'    => $sub->merchant_id,
                'period_start'   => $sub->renewal_date->subMonth(),
                'period_end'     => $sub->renewal_date,
                'base_calls'     => $limit,
                'overage_calls'  => $overage,
                'overage_charge' => $overage * $rate,
                'due_date'       => now()->addDays(30),
                'is_paid'        => false,
            ]);

            $sub->update([
                'api_calls_used' => 0,
                'overage_calls'  => 0,
                'renewal_date'   => now()->addMonth(),
                'is_blocked'     => false,
            ]);
        }
    });
});

// 🔐 Block subscriptions after 30 days of unpaid invoice
Schedule::daily()->call(function () {
    BillingLog::where('is_paid', false)
        ->whereDate('due_date', '<', now())
        ->get()
        ->each(function ($log) {
            Subscription::where('merchant_id', $log->merchant_id)
                ->update(['is_blocked' => true]);
        });
});
