<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DeleteDeviceRequest;
use App\Http\Requests\Api\RegisterDeviceRequest;
use App\Models\DeviceToken;
use App\Services\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeviceTokenController extends Controller
{
    /**
     * Register or reassign an Expo push notification device token.
     */
    public function register(RegisterDeviceRequest $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->input('expo_push_token');
        $platform = $request->input('platform');
        $deviceName = $request->input('device_name');
        $maxTokens = (int) config('push.max_tokens_per_account', 5);

        $deviceToken = DB::transaction(function () use ($user, $token, $platform, $deviceName, $maxTokens) {
            // Upsert / move token to current user if previously assigned to another user
            $record = DeviceToken::where('token', $token)->first();

            if ($record) {
                $record->tokenable_type = get_class($user);
                $record->tokenable_id = $user->id;
                $record->platform = $platform;
                $record->device_name = $deviceName;
                $record->last_seen_at = now();
                $record->save();
            } else {
                $record = DeviceToken::create([
                    'tokenable_type' => get_class($user),
                    'tokenable_id' => $user->id,
                    'token' => $token,
                    'platform' => $platform,
                    'device_name' => $deviceName,
                    'last_seen_at' => now(),
                ]);
            }

            // Prune oldest tokens if user exceeds max_tokens_per_account
            $userTokens = DeviceToken::where('tokenable_type', get_class($user))
                ->where('tokenable_id', $user->id)
                ->orderBy('last_seen_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            if ($userTokens->count() > $maxTokens) {
                $tokensToDelete = $userTokens->slice($maxTokens)->pluck('id');
                DeviceToken::whereIn('id', $tokensToDelete)->delete();
            }

            return $record;
        });

        return ApiResponse::success([
            'token' => $deviceToken->token,
            'platform' => $deviceToken->platform,
            'device_name' => $deviceToken->device_name,
            'last_seen_at' => $deviceToken->last_seen_at?->toIso8601String(),
        ], 'Device registered successfully.');
    }

    /**
     * Remove an Expo push notification device token for the current account.
     */
    public function delete(DeleteDeviceRequest $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->input('expo_push_token');

        DeviceToken::where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->id)
            ->where('token', $token)
            ->delete();

        return ApiResponse::success(new \stdClass(), 'Device unregistered successfully.');
    }
}
