<?php

namespace App\Console\Commands;

use App\Services\IngestCachePoolService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ProcessNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process cached notifications and send them to Expo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('custom.expo_token');
        if (! $token) {
            $this->error('Expo auth token not set!');

            return;
        }
        $batchSize = config('custom.expo_batch_size', 100);
        $maxRequestsPerRun = config('custom.expo_max_req_batch', 3);
        $requestsMade = 0;

        while ($requestsMade < $maxRequestsPerRun) {
            $notifications = $this->fetchNotifications($batchSize);

            if (empty($notifications)) {
                $this->info('No more notifications to process.');
                break;
            }

            $this->sendToExpo($notifications);
            $requestsMade++;
        }
    }

    protected function fetchNotifications($batchSize)
    {
        $notifications = [];

        for ($i = 0; $i < $batchSize; $i++) {
            $notification = IngestCachePoolService::pop();
            if ($notification) {
                $notifications[] = json_decode($notification, true);
            } else {
                break;
            }
        }

        return $notifications;
    }

    protected function sendToExpo($notifications)
    {
        $token = config('custom.expo_token');
        if (! $token) {
            return;
        }
        $messages = array_map(function ($notification) {
            return [
                'to' => $notification['token'],
                'sound' => 'default',
                'title' => 'Pixelfed',
                'ttl' => config('custom.expo_ttl', 1209600),
                'priority' => config('custom.expo_priority', 'high'),
                'badge' => 0,
                '_contentAvailable' => true,
                'body' => $notification['message'],
            ];
        }, $notifications);

        try {
            $response = Http::withToken($token)
                ->retry(3, function (int $attempt, Exception $exception) {
                    return $attempt * 150;
                }, function (Exception $exception, PendingRequest $request) {
                    return $exception instanceof ConnectionException;
                })
                ->post('https://exp.host/--/api/v2/push/send', $messages);

            if ($response->successful()) {
                $this->info('Successfully sent a batch of notifications.');
            } else {
                $this->error('Failed to send notifications. Re-queuing...');
                $this->requeueNotifications($notifications);
            }
        } catch (Exception|ConnectionException $e) {
            $this->error('Error sending notifications: '.$e->getMessage());
            $this->requeueNotifications($notifications);
        }
    }

    protected function requeueNotifications($notifications)
    {
        foreach ($notifications as $notification) {
            IngestCachePoolService::push($notification);
        }
    }
}
