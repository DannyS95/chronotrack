<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Domain\Reports\Models\Report;
use App\Domain\Reports\Policies\ReportPolicy;
use App\Domain\Tasks\Policies\TaskPolicy;

use App\Domain\Timers\Policies\TimerPolicy;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Report::class => ReportPolicy::class,
        Task::class   => TaskPolicy::class,
        Timer::class  => TimerPolicy::class,
    ];

     /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    public function boot()
    {
        $this->registerPolicies();
    }
}
