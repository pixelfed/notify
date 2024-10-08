<?php

namespace App\Console\Commands;

use App\Mail\NewInstance;
use App\Models\Instance;
use App\Services\InstanceService;
use App\Services\StatService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

class ManageInstance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:manage-instance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $task = select(
            label: 'Select an action:',
            options: ['Create', 'Read', 'Update'],
            required: true
        );

        switch ($task) {
            case 'Create':
                return $this->createTask();
                break;

            case 'Read':
                return $this->readTask();
                break;

            case 'Update':
                return $this->updateTask();
                break;
        }
        $this->info($task);
    }

    protected function createTask()
    {
        $domain = text(
            label: 'Enter domain',
            validate: ['domain' => 'required|regex:/^(?!:\/\/)([a-zA-Z0-9]+\.)*[a-zA-Z0-9][a-zA-Z0-9-]+(\.[a-z]{2,63})+$/']
        );

        $instance = Instance::updateOrCreate(['domain' => $domain]);

        $email = text(
            label: 'Enter email address',
            validate: ['email' => 'required|email|unique:instances,email']
        );

        $instance->email = $email;
        $instance->save();

        $supported = confirm('Is this instance supported?');

        if ($supported) {
            $instance->is_supported = true;
            $instance->save();
        }

        $allowed = confirm('Is this instance allowed?');

        if ($allowed) {
            $instance->is_allowed = true;
            $instance->save();
        }

        $instance->secret = InstanceService::keyGenerator($instance);
        $instance->save();
        InstanceService::clearKeys();
        InstanceService::getActiveDomains(true);

        $this->info('Successfully created instance!');
        $this->info('Secret:');
        $this->info($instance->secret);

        $supported = confirm('Do you want to send a welcome email?');

        if ($supported) {
            Mail::to($instance->email)->send(new NewInstance($instance));
        }
    }

    protected function readTask()
    {
        $id = search(
            label: 'Search for domain',
            options: fn (string $value) => strlen($value) > 0
                ? Instance::whereLike('domain', "%{$value}%")->pluck('domain', 'id')->all()
                : []
        );

        $instance = Instance::find($id);

        table(
            headers: ['Id', 'Domain', 'Supported', 'Allowed', 'Last Checked', 'Total Sent'],
            rows: [
                [
                    'Id' => $instance->id,
                    'Domain' => $instance->domain,
                    'Supported' => $instance->is_supported ? 'Yes' : 'No',
                    'Allowed' => $instance->is_allowed ? 'Yes' : 'No',
                    'Last Checked' => $instance->instance_last_checked_at ? $instance->instance_last_checked_at->format('c') : 'never',
                    'Total Sent' => StatService::get($instance->id) ?? 0,
                ],
            ]
        );

    }

    protected function updateTask()
    {
        $id = search(
            label: 'Search for domain',
            options: fn (string $value) => strlen($value) > 0
                ? Instance::whereLike('domain', "%{$value}%")->pluck('domain', 'id')->all()
                : []
        );

        $instance = Instance::find($id);

        table(
            headers: ['Id', 'Domain', 'Supported', 'Allowed', 'Last Checked'],
            rows: [
                [
                    'Id' => $instance->id,
                    'Domain' => $instance->domain,
                    'Supported' => $instance->is_supported ? 'Yes' : 'No',
                    'Allowed' => $instance->is_allowed ? 'Yes' : 'No',
                    'Last Checked' => $instance->instance_last_checked_at ? $instance->instance_last_checked_at->format('c') : 'never',
                ],
            ]
        );

        $task = select(
            label: 'Select an action:',
            options: ['Toggle Allowed', 'Toggle Supported', 'Reset Secret'],
            required: true
        );

        switch ($task) {
            case 'Toggle Allowed':
                $instance->is_allowed = $instance->is_allowed ? false : true;
                $instance->save();
                InstanceService::getActiveDomains(true);
                break;

            case 'Toggle Supported':
                $instance->is_supported = $instance->is_supported ? false : true;
                $instance->save();
                InstanceService::getActiveDomains(true);
                break;

            case 'Reset Secret':
                $instance->secret = InstanceService::keyGenerator($instance);
                $instance->save();
                InstanceService::clearKeys();
                InstanceService::getActiveDomains(true);
                $this->info('New Secret:');
                $this->info($instance->secret);
                break;
        }

    }
}
