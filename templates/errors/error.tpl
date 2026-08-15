{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="container py-5">
        <section class="card status-card border-0 shadow-sm mx-auto">
            <div class="card-body p-4 p-md-5">
                <span class="badge text-bg-danger">HTTP {$statusCode|escape}</span>
                <h1 class="display-5 fw-bold mt-4">{$pageTitle|escape}</h1>
                <p class="lead text-body-secondary">{$message|escape}</p>
                <a class="btn btn-primary mt-3" href="/">Return to the home page</a>
            </div>
        </section>
    </main>
{/block}
