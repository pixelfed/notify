<x-mail::message>
## Hello {{ $instance->domain }} admin,

We have approved your application for push.pixelfed.net gateway access.

Below is your API Key, please follow the instructions [available here](https://docs.pixelfed.org/running-pixelfed/push-notifications.html#configuration) for reference on how to setup your API key.

<x-mail::panel>
{{$instance->secret}}
</x-mail::panel>

----

## How To Enable Push Notifications

1) Add your API key to your .env

<x-mail::panel>
PIXELFED_PUSHGATEWAY_KEY="{{$instance->secret}}"
</x-mail::panel>

2) Re-cache config by running the following command:

<x-mail::panel>
php artisan config:cache
</x-mail::panel>

3) Run the following command and force a re-check:

<x-mail::panel>
php artisan app:push-gateway-refresh
</x-mail::panel>

<small>If you get an error that the command cannot be found, you need to update to the latest commits.</small>

4) Re-deploy or run the following command (if you are using the Horizon job queue):

<x-mail::panel>
php artisan horizon:terminate
</x-mail::panel>

Congratulations, you now have Push Notifications configured on your server!

<small>Please do not share your API key with anyone.</small>

Thanks,<br>
The Pixelfed Team
</x-mail::message>
