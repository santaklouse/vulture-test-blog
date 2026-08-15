<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$pageTitle|escape}</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header__inner">
            <a class="site-brand" href="/" aria-label="Vulture Blog home">Vulture Blog</a>
            <span class="site-header__tagline">Notes on building for the web</span>
        </div>
    </header>
    {block name="content"}{/block}
</body>
</html>
