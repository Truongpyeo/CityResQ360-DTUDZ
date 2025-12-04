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

namespace Tests\Feature\Admin;

use App\Models\QuanTriVien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected QuanTriVien $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test admin
        $this->admin = QuanTriVien::create([
            'ten_quan_tri' => 'Test Admin',
            'email' => 'test@admin.com',
            'mat_khau' => Hash::make('password'),
            'vai_tro' => QuanTriVien::VAI_TRO_SUPERADMIN,
            'trang_thai' => QuanTriVien::TRANG_THAI_ACTIVE,
        ]);
    }

    public function test_admin_can_view_login_page(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
    }

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $response = $this->post(route('admin.login'), [
            'email' => 'test@admin.com',
            'mat_khau' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_admin_cannot_login_with_incorrect_password(): void
    {
        $response = $this->post(route('admin.login'), [
            'email' => 'test@admin.com',
            'mat_khau' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_admin_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post(route('admin.login'), [
            'email' => 'nonexistent@admin.com',
            'mat_khau' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_locked_admin_cannot_login(): void
    {
        $this->admin->update(['trang_thai' => QuanTriVien::TRANG_THAI_LOCKED]);

        $response = $this->post(route('admin.login'), [
            'email' => 'test@admin.com',
            'mat_khau' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_admin_login_updates_last_login_time(): void
    {
        $oldLastLogin = $this->admin->lan_dang_nhap_cuoi;

        $this->post(route('admin.login'), [
            'email' => 'test@admin.com',
            'mat_khau' => 'password',
        ]);

        $this->admin->refresh();
        $this->assertNotEquals($oldLastLogin, $this->admin->lan_dang_nhap_cuoi);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_admin_can_login_with_username(): void
    {
        $response = $this->post(route('admin.login'), [
            'email' => 'Test Admin', // Using username instead of email
            'mat_khau' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_email_field_is_required(): void
    {
        $response = $this->post(route('admin.login'), [
            'mat_khau' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_field_is_required(): void
    {
        $response = $this->post(route('admin.login'), [
            'email' => 'test@admin.com',
        ]);

        $response->assertSessionHasErrors('mat_khau');
    }
}
