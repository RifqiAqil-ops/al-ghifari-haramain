<?php

namespace App\Services;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $token;
    protected ?string $phoneNumberId;
    protected string $apiVersion;
    protected bool $isConfigured;
    protected bool $sandboxMode;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token', env('WHATSAPP_TOKEN'));
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', env('WHATSAPP_PHONE_NUMBER_ID'));
        $this->apiVersion = config('services.whatsapp.api_version', env('WHATSAPP_API_VERSION', 'v20.0'));
        $this->sandboxMode = (bool) env('WHATSAPP_SANDBOX_MODE', true);

        $this->isConfigured = !empty($this->token) && !empty($this->phoneNumberId);
    }

    /**
     * Check if official WhatsApp Cloud API is connected.
     */
    public function isConnected(): bool
    {
        return $this->isConfigured && !$this->sandboxMode;
    }

    public function isSimulationMode(): bool
    {
        return !$this->isConfigured || $this->sandboxMode;
    }

    /**
     * Format Indonesian phone numbers to E.164 without '+' (e.g. 0812... -> 62812...)
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }
        if (str_starts_with($cleaned, '62')) {
            return $cleaned;
        }
        return '62' . $cleaned;
    }

    /**
     * Send text message.
     */
    public function sendTextMessage(string $to, string $message, string $type = 'manual', ?int $jamaahId = null): array
    {
        $formattedPhone = self::formatPhoneNumber($to);

        $messageRecord = WhatsAppMessage::create([
            'jamaah_id' => $jamaahId,
            'phone_number' => $formattedPhone,
            'message_type' => $type,
            'content' => $message,
            'status' => 'pending',
            'sent_by' => Auth::id(),
        ]);

        if ($this->isSimulationMode()) {
            $messageRecord->update([
                'status' => 'simulated',
                'sent_at' => now(),
                'response_payload' => [
                    'simulation' => true,
                    'note' => 'Mode simulasi / Credential API resmi belum dikonfigurasi di .env',
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            return [
                'success' => true,
                'status' => 'simulated',
                'simulation' => true,
                'message' => 'Pesan dicatat dalam mode simulasi (API resmi belum terhubung).',
                'record' => $messageRecord,
            ];
        }

        try {
            $endpoint = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

            $response = Http::withToken($this->token)
                ->timeout(15)
                ->post($endpoint, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $formattedPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                $payload = $response->json();
                $messageRecord->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_payload' => $payload,
                ]);

                return [
                    'success' => true,
                    'status' => 'sent',
                    'message' => 'Pesan WhatsApp berhasil dikirim melalui WhatsApp Cloud API.',
                    'record' => $messageRecord,
                ];
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Gagal mengirim pesan via WhatsApp API';

                $messageRecord->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'response_payload' => $errorData,
                ]);

                Log::error('WhatsApp Cloud API Error', ['response' => $errorData]);

                return [
                    'success' => false,
                    'status' => 'failed',
                    'message' => $errorMessage,
                    'record' => $messageRecord,
                ];
            }
        } catch (\Exception $e) {
            $messageRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'message' => $e->getMessage(),
                'record' => $messageRecord,
            ];
        }
    }

    /**
     * Send template-based message with variable replacements.
     */
    public function sendTemplateMessage(string $to, string $templateCode, array $variables = [], ?int $jamaahId = null): array
    {
        $template = WhatsAppTemplate::where('code', $templateCode)->where('is_active', true)->first();
        if (!$template) {
            return [
                'success' => false,
                'message' => "Template WhatsApp dengan kode '{$templateCode}' tidak ditemukan atau nonaktif.",
            ];
        }

        $content = $template->body;
        foreach ($variables as $key => $value) {
            $content = str_replace('{' . $key . '}', (string) $value, $content);
        }

        return $this->sendTextMessage($to, $content, $template->category, $jamaahId);
    }
}
