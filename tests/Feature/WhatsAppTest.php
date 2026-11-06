<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WhatsAppService;
use Tests\TestCase;

class WhatsAppTest extends TestCase
{
    public function test_whatsapp_service_simulation_mode_records_message(): void
    {
        $waService = app(WhatsAppService::class);

        $phone = '081298765432';
        $content = 'Assalamu\'alaikum, ini adalah pesan uji sistem Al Ghifari.';

        $result = $waService->sendTextMessage($phone, $content, 'manual');

        $this->assertTrue($result['success']);
        $this->assertEquals('simulated', $result['status']);
        $this->assertTrue($result['simulation']);

        $this->assertDatabaseHas('whatsapp_messages', [
            'phone_number' => '6281298765432',
            'content' => $content,
            'status' => 'simulated',
        ]);
    }

    public function test_authenticated_user_can_view_whatsapp_center(): void
    {
        $user = User::where('email', 'superadmin@alghifari.com')->first();
        $response = $this->actingAs($user)->get(route('whatsapp.index'));
        $response->assertStatus(200);
        $response->assertSee('WhatsApp Business Cloud API');
    }
}
