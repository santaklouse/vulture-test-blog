<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$pageTitle|escape}</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-body-tertiary">
    <header class="navbar bg-white border-bottom">
        <div class="container py-2">
            <a class="navbar-brand site-brand fw-bold" href="/" aria-label="Vulture Blog home">
                Vulture Blog
            </a>
            <span class="navbar-text d-none d-sm-inline">Notes on building for the web</span>
        </div>
    </header>
    {block name="content"}{/block}
</body>
</html>
