<?php

namespace App\Services\Api;

use App\Jobs\Api\SendExpoPushNotificationJob;
use App\Models\DeviceToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PushService
{
    /**
     * Send a push notification to a specific account (User, Seller, or Rider).
     */
    public function sendToAccount(Model $account, string $title, string $body, array $data = [], ?int $dedupeSeconds = null): void
    {
        if (!config('push.enabled', false)) {
            return;
        }

        try {
            // Deduplication check
            if ($this->isDuplicate($account, $data, $dedupeSeconds)) {
                return;
            }

            $tokens = DeviceToken::where('tokenable_type', get_class($account))
                ->where('tokenable_id', $account->id)
                ->pluck('token')
                ->all();

            if (empty($tokens)) {
                return;
            }

            $this->sendToTokens($tokens, $title, $body, $data);
        } catch (\Throwable $e) {
            Log::error('PushService sendToAccount error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send a push notification to multiple accounts.
     */
    public function sendToMany(iterable $accounts, string $title, string $body, array $data = []): void
    {
        if (!config('push.enabled', false)) {
            return;
        }

        try {
            $accountGroups = [];
            foreach ($accounts as $account) {
                if ($account instanceof Model) {
                    $accountGroups[get_class($account)][] = $account->id;
                }
            }

            if (empty($accountGroups)) {
                return;
            }

            $tokens = [];
            foreach ($accountGroups as $type => $ids) {
                $foundTokens = DeviceToken::where('tokenable_type', $type)
                    ->whereIn('tokenable_id', $ids)
                    ->pluck('token')
                    ->all();
                $tokens = array_merge($tokens, $foundTokens);
            }

            $tokens = array_unique($tokens);

            if (empty($tokens)) {
                return;
            }

            $this->sendToTokens($tokens, $title, $body, $data);
        } catch (\Throwable $e) {
            Log::error('PushService sendToMany error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send push notification to an explicit list of Expo tokens.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if (!config('push.enabled', false) || empty($tokens)) {
            return;
        }

        try {
            $channelId = config('push.android_channel_id', 'orders');
            $stringData = array_map(fn($v) => is_scalar($v) ? (string) $v : json_encode($v), $data);

            $messages = [];
            foreach ($tokens as $token) {
                $messages[] = [
                    'to' => $token,
                    'title' => $title,
                    'body' => $body,
                    'data' => $stringData,
                    'sound' => 'default',
                    'priority' => 'high',
                    'channelId' => $channelId,
                ];
            }

            // Chunk in batches of 100 as per Expo API specifications
            $batches = array_chunk($messages, 100);
            foreach ($batches as $batch) {
                if (app()->runningUnitTests()) {
                    SendExpoPushNotificationJob::dispatchSync($batch);
                } else {
                    SendExpoPushNotificationJob::dispatchAfterResponse($batch);
                }
            }
        } catch (\Throwable $e) {
            Log::error('PushService sendToTokens error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check and record deduplication key in cache.
     */
    protected function isDuplicate(Model $account, array $data, ?int $customDedupeSeconds = null): bool
    {
        $orderId = $data['order_id'] ?? null;
        $type = $data['type'] ?? null;

        if (!$orderId || !$type) {
            return false;
        }

        $dedupeSeconds = $customDedupeSeconds ?? (int) config('push.dedupe_seconds', 10);
        $typeKey = get_class($account);
        $status = $data['status'] ?? '';
        $key = "push_dedupe:{$typeKey}:{$account->id}:{$orderId}:{$type}" . ($status !== '' ? ":{$status}" : '');

        if (Cache::has($key)) {
            Log::info("Push suppressed by deduplication window: {$key}");
            return true;
        }

        Cache::put($key, true, now()->addSeconds($dedupeSeconds));
        return false;
    }
}
