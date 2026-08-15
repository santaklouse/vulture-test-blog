{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="page-shell page-content">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="/">Home</a>
            <span aria-hidden="true">/</span>
            <span>{$category.name|escape}</span>
        </nav>

        <header class="page-heading">
            <span class="page-heading__eyebrow">Category · {$totalPosts|escape} articles</span>
            <h1>{$category.name|escape}</h1>
            {if $category.description}
                <p>{$category.description|escape}</p>
            {/if}
        </header>

        <div class="listing-toolbar">
            <span>Sort by</span>
            <nav class="sort-tabs" aria-label="Article sorting">
                <a
                    class="sort-tabs__link{if $sort === 'date'} sort-tabs__link--active{/if}"
                    href="{$sortLinks.date|escape}"
                >Newest</a>
                <a
                    class="sort-tabs__link{if $sort === 'views'} sort-tabs__link--active{/if}"
                    href="{$sortLinks.views|escape}"
                >Most viewed</a>
            </nav>
        </div>

        {if $posts}
            <div class="post-grid post-grid--listing">
                {foreach $posts as $post}
                    {include file="components/post-card.tpl" post=$post}
                {/foreach}
            </div>

            {if $pagination.totalPages > 1}
                <nav class="pagination" aria-label="Pagination">
                    {if $pagination.previousUrl}
                        <a class="pagination__link pagination__link--direction" href="{$pagination.previousUrl|escape}">
                            Previous
                        </a>
                    {else}
                        <span class="pagination__link pagination__link--direction pagination__link--disabled">Previous</span>
                    {/if}

                    <div class="pagination__pages">
                        {foreach $pagination.pages as $page}
                            {if $page.current}
                                <span class="pagination__link pagination__link--current" aria-current="page">
                                    {$page.number|escape}
                                </span>
                            {else}
                                <a class="pagination__link" href="{$page.url|escape}">{$page.number|escape}</a>
                            {/if}
                        {/foreach}
                    </div>

                    {if $pagination.nextUrl}
                        <a class="pagination__link pagination__link--direction" href="{$pagination.nextUrl|escape}">
                            Next
                        </a>
                    {else}
                        <span class="pagination__link pagination__link--direction pagination__link--disabled">Next</span>
                    {/if}
                </nav>
            {/if}
        {else}
            <section class="empty-state">
                <h2>No articles yet</h2>
                <p>This category does not contain published articles.</p>
            </section>
        {/if}
    </main>
{/block}
