{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="container py-4 pb-5">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">{$category.name|escape}</li>
            </ol>
        </nav>

        <header class="col-lg-8 py-5">
            <span class="text-primary fw-semibold text-uppercase small">
                Category · {$totalPosts|escape} articles
            </span>
            <h1 class="display-4 fw-bold mt-3">{$category.name|escape}</h1>
            {if $category.description}
                <p class="lead text-body-secondary mb-0">{$category.description|escape}</p>
            {/if}
        </header>

        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 p-3 mb-4 bg-white border rounded">
            <span class="fw-semibold">Sort by</span>
            <nav class="btn-group" aria-label="Article sorting">
                <a
                    class="btn btn-sm {if $sort === 'date'}btn-primary{else}btn-outline-secondary{/if}"
                    href="{$sortLinks.date|escape}"
                    {if $sort === 'date'}aria-current="page"{/if}
                >Newest</a>
                <a
                    class="btn btn-sm {if $sort === 'views'}btn-primary{else}btn-outline-secondary{/if}"
                    href="{$sortLinks.views|escape}"
                    {if $sort === 'views'}aria-current="page"{/if}
                >Most viewed</a>
            </nav>
        </div>

        {if $posts}
            <div class="row g-4">
                {foreach $posts as $post}
                    <div class="col-12 col-md-6 col-xl-4">
                        {include file="components/post-card.tpl" post=$post}
                    </div>
                {/foreach}
            </div>

            {if $pagination.totalPages > 1}
                <nav class="mt-5" aria-label="Pagination">
                    <ul class="pagination justify-content-center flex-wrap">
                        <li class="page-item{if !$pagination.previousUrl} disabled{/if}">
                            {if $pagination.previousUrl}
                                <a class="page-link" href="{$pagination.previousUrl|escape}">Previous</a>
                            {else}
                                <span class="page-link">Previous</span>
                            {/if}
                        </li>
                        {foreach $pagination.pages as $page}
                            {if $page.current}
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{$page.number|escape}</span>
                                </li>
                            {else}
                                <li class="page-item">
                                    <a class="page-link" href="{$page.url|escape}">{$page.number|escape}</a>
                                </li>
                            {/if}
                        {/foreach}
                        <li class="page-item{if !$pagination.nextUrl} disabled{/if}">
                            {if $pagination.nextUrl}
                                <a class="page-link" href="{$pagination.nextUrl|escape}">Next</a>
                            {else}
                                <span class="page-link">Next</span>
                            {/if}
                        </li>
                    </ul>
                </nav>
            {/if}
        {else}
            <section class="alert alert-light border text-center py-5">
                <h2 class="h4">No articles yet</h2>
                <p class="mb-0">This category does not contain published articles.</p>
            </section>
        {/if}
    </main>
{/block}
