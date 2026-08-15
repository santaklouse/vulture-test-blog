{extends file="layouts/base.tpl"}

{block name="content"}
    <main class="page-shell">
        <section class="home-hero">
            <span class="home-hero__eyebrow">Plain PHP · MySQL · Smarty</span>
            <h1>Practical ideas for building better web applications.</h1>
            <p>Explore recent articles about backend development, databases, interfaces, and deployment.</p>
        </section>

        {if $categorySections}
            <div class="category-list">
                {foreach $categorySections as $section}
                    <section class="category-section" aria-labelledby="category-{$section.slug|escape}">
                        <header class="category-section__header">
                            <div>
                                <h2 id="category-{$section.slug|escape}">{$section.name|escape}</h2>
                                {if $section.description}
                                    <p>{$section.description|escape}</p>
                                {/if}
                            </div>
                            <a class="button button--secondary" href="/categories/{$section.slug|escape:'url'}">
                                All articles
                            </a>
                        </header>

                        <div class="post-grid">
                            {foreach $section.posts as $post}
                                {include file="components/post-card.tpl" post=$post}
                            {/foreach}
                        </div>
                    </section>
                {/foreach}
            </div>
        {else}
            <section class="empty-state">
                <h2>No articles yet</h2>
                <p>New posts will appear here after the database is seeded.</p>
            </section>
        {/if}
    </main>
{/block}
