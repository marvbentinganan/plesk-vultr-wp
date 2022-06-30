@component('mail::message')
# Hello Administrator,

Automated Provisioning was done successfully and a new Plesk Instance was deployed for {{ $customer->name }}.

@component('mail::panel')
## Plesk Panel
### Login Information

URL: [{{ $panel_url }}]({{ $panel_url }})

Username: {{ $panel_username }}

Password: {{ $panel_password }}


@component('mail::button', ['url' => $panel_url])
Visit Plesk Panel
@endcomponent

## WordPress Site
### Login Information

URL: [{{ $site_url }}]({{ $site_url }})

Username: {{ $site_email }}

Password: {{ $site_password }}


@component('mail::button', ['url' => $site_url])
Visit WordPress Site
@endcomponent

@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
