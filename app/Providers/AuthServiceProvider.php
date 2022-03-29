<?php

namespace App\Providers;

use App\Models\{
    Classroom,
    Exam,
};
use App\Policies\{
    ClassroomPolicy,
    ExamPolicy
};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        Classroom::class => ClassroomPolicy::class,
        Exam::class => ExamPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        
    }
}
