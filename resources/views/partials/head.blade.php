<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'NSUK Partnership Management') : config('app.name', 'NSUK Partnership Management') }}
</title>

<link rel="icon" href="https://ug.nsuk.edu.ng/api/global/logo" type="image/png">
<link rel="apple-touch-icon" href="https://ug.nsuk.edu.ng/api/global/logo">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
