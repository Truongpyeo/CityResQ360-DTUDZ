<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
