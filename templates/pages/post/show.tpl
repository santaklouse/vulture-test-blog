{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="container py-4 pb-5">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                {if $breadcrumbCategory}
                    <li class="breadcrumb-item">
                        <a href="/categories/{$breadcrumbCategory.slug|escape:'url'}">
                            {$breadcrumbCategory.name|escape}
                        </a>
                    </li>
                {/if}
                <li class="breadcrumb-item active" aria-current="page">{$post.title|escape}</li>
            </ol>
        </nav>

        <article class="article mx-auto">
            <header class="text-center py-5">
                {if $categories}
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                        {foreach $categories as $category}
                            <a
                                class="badge rounded-pill text-bg-primary text-decoration-none"
                                href="/categories/{$category.slug|escape:'url'}"
                            >{$category.name|escape}</a>
                        {/foreach}
                    </div>
                {/if}

                <h1 class="display-3 fw-bold">{$post.title|escape}</h1>
                <p class="lead text-body-secondary mx-auto mb-0">{$post.description|escape}</p>
                <div class="d-flex justify-content-center gap-4 mt-4 text-body-secondary small text-uppercase">
                    <time datetime="{$post.publishedAt|escape}">{$post.publishedAtLabel|escape}</time>
                    <span>{$post.viewsCount|escape} views</span>
                </div>
            </header>

            {if $post.image}
                <img
                    class="article__image img-fluid rounded shadow-sm"
                    src="{$post.image|escape}"
                    alt=""
                    width="1200"
                    height="720"
                >
            {/if}

            <div class="article__body mt-5 mx-auto">
                {foreach $post.bodyParagraphs as $paragraph}
                    <p>{$paragraph|escape}</p>
                {/foreach}
            </div>
        </article>

        {if $relatedPosts}
            <section class="border-top mt-5 pt-5" aria-labelledby="related-posts-title">
                <header class="mb-4">
                    <span class="text-primary fw-semibold text-uppercase small">Continue reading</span>
                    <h2 class="h3 mt-2" id="related-posts-title">Related articles</h2>
                </header>
                <div class="row g-4">
                    {foreach $relatedPosts as $post}
                        <div class="col-12 col-md-6 col-xl-4">
                            {include file="components/post-card.tpl" post=$post}
                        </div>
                    {/foreach}
                </div>
            </section>
        {/if}
    </main>
{/block}
