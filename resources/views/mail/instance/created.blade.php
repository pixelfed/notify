<x-mail::message>
## Dear {{ $instance->domain }} admin,

We have approved your application for push.pixelfed.net gateway access.

Click this button to add the Gateway API key to your admin dashboard.

<x-mail::button :url="$instance->getAdminUrl()">
Enable Push Notifications
</x-mail::button>

<small>If the link redirects to a blank page, you may need to update to the latest commits and/or login with an admin account.</small>

<small>Please do not share your API key with anyone.</small>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
