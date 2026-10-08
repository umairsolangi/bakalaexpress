<?php

namespace App\Jobs\Api;

use App\Models\DeviceToken;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendExpoPushNotificationJob
{
    use Dispatchable;

    /**
     * @param array $messages Array of formatted Expo push messages (max 100 per batch)
     */
    public function __construct(
        public array $messages
    ) {}

    /**
     * Execute the synchronous push dispatch.
     */
    public function handle(): void
    {
        if (empty($this->messages) || !config('push.enabled', false)) {
            return;
        }

        $url = config('push.expo_url', 'https://exp.host/--/api/v2/push/send');
        $timeout = (int) config('push.timeout', 5);
        $accessToken = config('push.access_token');

        try {
            $request = Http::timeout($timeout)
                ->acceptJson()
                ->asJson();

            if (!empty($accessToken)) {
                $request->withToken($accessToken);
            }

            $response = $request->post($url, $this->messages);

            if ($response->successful()) {
                $tickets = $response->json('data') ?? [];
                $this->processTickets($tickets);
            } else {
                Log::warning('Expo push service responded with HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Expo push HTTP request failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process tickets returned by Expo and delete invalid/unregistered device tokens.
     */
    protected function processTickets(array $tickets): void
    {
        foreach ($tickets as $index => $ticket) {
            if (isset($ticket['status']) && $ticket['status'] === 'error') {
                $errorCode = $ticket['details']['error'] ?? null;
                $targetToken = $this->messages[$index]['to'] ?? null;

                if ($errorCode === 'DeviceNotRegistered' && $targetToken) {
                    DeviceToken::where('token', $targetToken)->delete();
                    Log::info("Deleted unregistered Expo push token: {$targetToken}");
                } else {
                    Log::warning('Expo push ticket error', [
                        'token' => $targetToken,
                        'ticket' => $ticket,
                    ]);
                }
            }
        }
    }
}
