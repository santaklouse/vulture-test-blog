<article class="card h-100 border-0 shadow-sm">
    <a href="/posts/{$post.slug|escape:'url'}" tabindex="-1">
        {if $post.image}
            <img
                class="card-img-top post-card-image"
                src="{$post.image|escape}"
                alt=""
                width="1200"
                height="720"
                loading="lazy"
            >
        {else}
            <span class="card-img-top post-card-image d-flex align-items-center justify-content-center bg-dark text-white">
                Vulture Blog
            </span>
        {/if}
    </a>
    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between gap-3 text-body-secondary small mb-3">
            <time datetime="{$post.publishedAt|escape}">{$post.publishedAtLabel|escape}</time>
            <span>{$post.viewsCount|escape} views</span>
        </div>
        <h3 class="h5 card-title">
            <a class="text-body text-decoration-none" href="/posts/{$post.slug|escape:'url'}">
                {$post.title|escape}
            </a>
        </h3>
        <p class="card-text text-body-secondary mb-0">{$post.description|escape}</p>
    </div>
</article>
