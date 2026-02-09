<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Domain\Reports\Models\Report;
use App\Domain\Reports\Policies\ReportPolicy;
use App\Interface\Auth\Policies\GoalPolicy;
use App\Interface\Auth\Policies\TaskPolicy;
use App\Interface\Auth\Policies\TimerPolicy;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Report::class => ReportPolicy::class,
        Goal::class    => GoalPolicy::class,
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
