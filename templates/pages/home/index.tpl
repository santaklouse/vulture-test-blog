{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="status-card">
        <span class="status-card__badge">MVC is ready</span>
        <h1>{$pageTitle|escape}</h1>
        <p>The application now uses a front controller, regex router, controllers, responses, and Smarty views.</p>
        <div class="status-card__stack">
            Path {$currentPath|escape} · PHP {$phpVersion|escape} · Smarty {$smartyVersion|escape} · Nginx · MySQL
        </div>
    </main>
{/block}

