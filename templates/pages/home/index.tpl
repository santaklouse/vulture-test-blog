{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="container py-5">
        <section class="col-lg-9 py-lg-5 mb-5">
            <span class="text-primary fw-semibold text-uppercase small">Plain PHP · MySQL · Smarty</span>
            <h1 class="display-3 fw-bold mt-3">Practical ideas for building better web applications.</h1>
            <p class="lead text-body-secondary mb-0">
                Explore recent articles about backend development, databases, interfaces, and deployment.
            </p>
        </section>

        {if $categorySections}
            <div class="vstack gap-5 pb-5">
                {foreach $categorySections as $section}
                    <section aria-labelledby="category-{$section.slug|escape}">
                        <header class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-4">
                            <div>
                                <h2 class="h3 mb-2" id="category-{$section.slug|escape}">{$section.name|escape}</h2>
                                {if $section.description}
                                    <p class="text-body-secondary mb-0">{$section.description|escape}</p>
                                {/if}
                            </div>
                            <a class="btn btn-outline-primary flex-shrink-0" href="/categories/{$section.slug|escape:'url'}">
                                All articles
                            </a>
                        </header>

                        <div class="row g-4">
                            {foreach $section.posts as $post}
                                <div class="col-12 col-md-6 col-xl-4">
                                    {include file="components/post-card.tpl" post=$post}
                                </div>
                            {/foreach}
                        </div>
                    </section>
                {/foreach}
            </div>
        {else}
            <section class="alert alert-light border text-center py-5">
                <h2 class="h4">No articles yet</h2>
                <p class="mb-0">New posts will appear here after the database is seeded.</p>
            </section>
        {/if}
    </main>
{/block}
