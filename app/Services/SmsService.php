<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS message using the configured provider.
     *
     * @return array{success: bool, message: string}
     */
    public function send(string $phone, string $message, ?int $tenantId = null): array
    {
        $provider = Setting::get('sms_provider', 'none');

        if ($provider === 'none') {
            return ['success' => false, 'message' => 'No SMS provider configured.'];
        }

        $log = SmsLog::create([
            'tenant_id' => $tenantId,
            'phone'     => $phone,
            'message'   => $message,
            'provider'  => $provider,
            'status'    => 'queued',
        ]);

        try {
            $result = match ($provider) {
                'twilio'       => $this->sendViaTwilio($phone, $message),
                'msg91'        => $this->sendViaMsg91($phone, $message),
                'ssl_wireless' => $this->sendViaSslWireless($phone, $message),
                'custom_http'  => $this->sendViaCustomHttp($phone, $message),
                default        => ['success' => false, 'response' => 'Unknown provider: ' . $provider],
            };

            $log->update([
                'status'            => $result['success'] ? 'sent' : 'failed',
                'provider_response' => $result['response'] ?? null,
            ]);

            return [
                'success' => $result['success'],
                'message' => $result['success'] ? 'SMS sent successfully.' : ('SMS failed: ' . ($result['response'] ?? 'Unknown error')),
            ];
        } catch (\Throwable $e) {
            Log::error('SMS send failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            $log->update(['status' => 'failed', 'provider_response' => $e->getMessage()]);
            return ['success' => false, 'message' => 'SMS failed: ' . $e->getMessage()];
        }
    }

    /* --------------------------------------------------------
     * Provider Implementations
     * ------------------------------------------------------ */

    protected function sendViaTwilio(string $phone, string $message): array
    {
        $sid   = Setting::get('sms_twilio_sid');
        $token = Setting::get('sms_twilio_token');
        $from  = Setting::get('sms_twilio_from');

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To'   => $phone,
                'From' => $from,
                'Body' => $message,
            ]);

        return [
            'success'  => $response->successful(),
            'response' => $response->body(),
        ];
    }

    protected function sendViaMsg91(string $phone, string $message): array
    {
        $authKey    = Setting::get('sms_msg91_auth_key');
        $senderId   = Setting::get('sms_msg91_sender_id');
        $templateId = Setting::get('sms_msg91_template_id');
        $route      = Setting::get('sms_msg91_route', '4'); // transactional

        $response = Http::withHeaders(['authkey' => $authKey])
            ->post('https://control.msg91.com/api/v5/flow/', [
                'template_id' => $templateId,
                'sender'      => $senderId,
                'short_url'   => '0',
                'mobiles'     => $phone,
                'SMS'         => $message,
            ]);

        return [
            'success'  => $response->successful(),
            'response' => $response->body(),
        ];
    }

    protected function sendViaSslWireless(string $phone, string $message): array
    {
        $apiToken = Setting::get('sms_ssl_api_token');
        $sid      = Setting::get('sms_ssl_sid');

        $response = Http::post('https://smsplus.sslwireless.com/api/v3/send-sms', [
            'api_token' => $apiToken,
            'sid'       => $sid,
            'msisdn'    => $phone,
            'sms'       => $message,
            'csms_id'   => uniqid('bms_'),
        ]);

        $body = $response->json();

        return [
            'success'  => ($body['status'] ?? '') === 'SUCCESS',
            'response' => $response->body(),
        ];
    }

    protected function sendViaCustomHttp(string $phone, string $message): array
    {
        $url        = Setting::get('sms_custom_url');
        $method     = strtoupper(Setting::get('sms_custom_method', 'POST'));
        $phoneParam = Setting::get('sms_custom_phone_param', 'phone');
        $msgParam   = Setting::get('sms_custom_message_param', 'message');
        $extraJson  = Setting::get('sms_custom_extra_params', '{}');

        $extra = json_decode($extraJson, true) ?: [];
        $payload = array_merge($extra, [
            $phoneParam => $phone,
            $msgParam   => $message,
        ]);

        $response = $method === 'GET'
            ? Http::get($url, $payload)
            : Http::post($url, $payload);

        return [
            'success'  => $response->successful(),
            'response' => $response->body(),
        ];
    }
}
