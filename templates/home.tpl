<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$pageTitle|escape}</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <main class="status-card">
        <span class="status-card__badge">It Works!</span>
        <h1>{$pageTitle|escape}</h1>
        <p>Wello world</p>
        <div class="status-card__stack">
            PHP {$phpVersion|escape} · Smarty {$smartyVersion|escape} · Nginx · MySQL
        </div>
    </main>
</body>
</html>
