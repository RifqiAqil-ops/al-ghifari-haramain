<?php

namespace Tests\Feature;

use App\Models\Jamaah;
use App\Models\User;
use Tests\TestCase;

class JamaahTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('email', 'superadmin@alghifari.com')->first();
    }

    public function test_authenticated_user_can_view_jamaah_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('jamaahs.index'));
        $response->assertStatus(200);
        $response->assertSee('Direktori Jamaah');
    }

    public function test_authenticated_user_can_create_jamaah(): void
    {
        $nik = '317101' . rand(1000000000, 9999999999);
        $jamaahData = [
            'name' => 'Budi Santoso Uji Otomatis',
            'nik' => $nik,
            'gender' => 'L',
            'birth_place' => 'Jakarta',
            'birth_date' => '1985-05-12',
            'phone' => '0812' . rand(10000000, 99999999),
            'address' => 'Jl. Kebon Jeruk No. 12 Jakarta Barat',
            'city' => 'Jakarta Barat',
            'province' => 'DKI Jakarta',
            'passport_number' => 'X' . rand(1000000, 9999999),
            'passport_expiry_date' => now()->addYears(5)->format('Y-m-d'),
            'passport_issued_place' => 'Jakarta',
            'status' => 'aktif',
        ];

        $response = $this->actingAs($this->user)->post(route('jamaahs.store'), $jamaahData);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('jamaahs', [
            'nik' => $nik,
            'name' => 'Budi Santoso Uji Otomatis',
        ]);
    }

    public function test_authenticated_user_can_view_jamaah_profile(): void
    {
        $jamaah = Jamaah::first();
        if ($jamaah) {
            $response = $this->actingAs($this->user)->get(route('jamaahs.show', $jamaah));
            $response->assertStatus(200);
            $response->assertSee($jamaah->name);
        }
    }
}
