{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="status-card">
        <span class="status-card__badge status-card__badge--error">HTTP {$statusCode|escape}</span>
        <h1>{$pageTitle|escape}</h1>
        <p>{$message|escape}</p>
        <a class="status-card__link" href="/">Return to the home page</a>
    </main>
{/block}

