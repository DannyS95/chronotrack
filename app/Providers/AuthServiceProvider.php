<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Domain\Reports\Models\Report;
use App\Domain\Reports\Policies\ReportPolicy;
use App\Domain\Task\Models\Task;
use App\Domain\Tasks\Policies\TaskPolicy;

use App\Domain\Timers\Models\Timer;
use App\Domain\Timers\Policies\TimerPolicy;

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
