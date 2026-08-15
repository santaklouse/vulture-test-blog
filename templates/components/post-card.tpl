<article class="post-card">
    <a class="post-card__image-link" href="/posts/{$post.slug|escape:'url'}" tabindex="-1">
        {if $post.image}
            <img
                class="post-card__image"
                src="{$post.image|escape}"
                alt=""
                width="1200"
                height="720"
                loading="lazy"
            >
        {else}
            <span class="post-card__image post-card__image--empty">Vulture Blog</span>
        {/if}
    </a>
    <div class="post-card__body">
        <div class="post-card__meta">
            <time datetime="{$post.publishedAt|escape}">{$post.publishedAtLabel|escape}</time>
            <span>{$post.viewsCount|escape} views</span>
        </div>
        <h3>
            <a href="/posts/{$post.slug|escape:'url'}">{$post.title|escape}</a>
        </h3>
        <p>{$post.description|escape}</p>
    </div>
</article>
