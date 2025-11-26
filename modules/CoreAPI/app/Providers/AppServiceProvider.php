<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\PhanAnh;
use App\Models\NguoiDung;
use App\Models\CoQuanXuLy;
use App\Models\QuanTriVien;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use App\Policies\AgencyPolicy;
use App\Policies\AdminPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        PhanAnh::class => ReportPolicy::class,
        NguoiDung::class => UserPolicy::class,
        CoQuanXuLy::class => AgencyPolicy::class,
        QuanTriVien::class => AdminPolicy::class,
    ];

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
        // Register policies
        Gate::policy(PhanAnh::class, ReportPolicy::class);
        Gate::policy(NguoiDung::class, UserPolicy::class);
        Gate::policy(CoQuanXuLy::class, AgencyPolicy::class);
        Gate::policy(QuanTriVien::class, AdminPolicy::class);
    }
}
