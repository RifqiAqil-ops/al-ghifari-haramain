<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InitializationTest extends TestCase
{
    /**
     * Test home landing page redirects guests to login page which displays brand identity.
     */
    public function test_welcome_page_redirects_and_displays_brand_identity(): void
    {
        $response = $this->get('/');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('AL GHIFARI HARAMAIN');
        $loginResponse->assertSee('UMROH &amp; HAJI SPECIALIST', false);
    }

    /**
     * Test MySQL database connection to al_ghifari_haramain_db is operational.
     */
    public function test_mysql_database_connection_is_operational(): void
    {
        $pdo = DB::connection('mysql')->getPdo();
        $this->assertNotNull($pdo, 'Koneksi PDO ke MySQL harus berhasil.');

        $databaseName = DB::connection('mysql')->getDatabaseName();
        $this->assertEquals('al_ghifari_haramain_db', $databaseName, 'Database MySQL harus al_ghifari_haramain_db.');

        $hasUsersTable = DB::connection('mysql')->getSchemaBuilder()->hasTable('users');
        $this->assertTrue($hasUsersTable, 'Tabel users bawaan Laravel harus sudah dimigrasi di MySQL.');
    }

    /**
     * Test that the official brand logo file exists and is accessible.
     */
    public function test_brand_logo_exists(): void
    {
        $logoPath = public_path('assets/brand/logo.png');
        $this->assertFileExists($logoPath, 'Logo resmi harus tersedia di public/assets/brand/logo.png.');
    }
}
