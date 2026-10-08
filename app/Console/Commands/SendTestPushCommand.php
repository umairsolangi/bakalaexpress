<?php

namespace App\Console\Commands;

use App\Models\Rider;
use App\Models\Seller;
use App\Models\User;
use App\Services\Api\PushService;
use Illuminate\Console\Command;

class SendTestPushCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'push:test
                            {role : The account role (customer, seller, rider)}
                            {id : The database ID of the account}
                            {--title= : Custom push notification title}
                            {--body= : Custom push notification body}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test Expo push notification to a specific account';

    /**
     * Execute the console command.
     */
    public function handle(PushService $pushService): int
    {
        if (!config('push.enabled', false)) {
            $this->warn('Push notifications are currently disabled (PUSH_ENABLED=false). No push was sent.');
            return 1;
        }

        $role = strtolower($this->argument('role'));
        $id = (int) $this->argument('id');

        $account = match ($role) {
            'customer' => User::find($id),
            'seller' => Seller::find($id),
            'rider' => Rider::find($id),
            default => null,
        };

        if (!$account) {
            $this->error("Account not found for role '{$role}' with ID {$id}.");
            return 1;
        }

        $title = $this->option('title') ?: 'Test Push Notification';
        $body = $this->option('body') ?: 'This is a test notification from Bakala Express.';

        $pushService->sendToAccount($account, $title, $body, [
            'type' => 'test',
            'role' => $role,
        ]);

        $this->info("Test push dispatched for {$role} #{$id}.");
        return 0;
    }
}
