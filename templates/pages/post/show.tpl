{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="page-shell article-page">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="/">Home</a>
            {if $breadcrumbCategory}
                <span aria-hidden="true">/</span>
                <a href="/categories/{$breadcrumbCategory.slug|escape:'url'}">
                    {$breadcrumbCategory.name|escape}
                </a>
            {/if}
            <span aria-hidden="true">/</span>
            <span aria-current="page">{$post.title|escape}</span>
        </nav>

        <article class="article">
            <header class="article__header">
                {if $categories}
                    <div class="category-chips">
                        {foreach $categories as $category}
                            <a href="/categories/{$category.slug|escape:'url'}">{$category.name|escape}</a>
                        {/foreach}
                    </div>
                {/if}

                <h1>{$post.title|escape}</h1>
                <p class="article__lead">{$post.description|escape}</p>
                <div class="article__meta">
                    <time datetime="{$post.publishedAt|escape}">{$post.publishedAtLabel|escape}</time>
                    <span>{$post.viewsCount|escape} views</span>
                </div>
            </header>

            {if $post.image}
                <img
                    class="article__image"
                    src="{$post.image|escape}"
                    alt=""
                    width="1200"
                    height="720"
                >
            {/if}

            <div class="article__body">
                {foreach $post.bodyParagraphs as $paragraph}
                    <p>{$paragraph|escape}</p>
                {/foreach}
            </div>
        </article>

        {if $relatedPosts}
            <section class="related-posts" aria-labelledby="related-posts-title">
                <header class="related-posts__header">
                    <span>Continue reading</span>
                    <h2 id="related-posts-title">Related articles</h2>
                </header>
                <div class="post-grid">
                    {foreach $relatedPosts as $post}
                        {include file="components/post-card.tpl" post=$post}
                    {/foreach}
                </div>
            </section>
        {/if}
    </main>
{/block}
