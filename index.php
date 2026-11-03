<?php

use JonathanNicol\SimpleSeo\PageMeta;

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Response;
use Kirby\Filesystem\F;

/*
Autoloader

Kirby ships with F::loadClasses(), a small autoloader that takes a map of class 
names to file paths. Only loaded the first time the class is actually needed.

Key is lowercased class namespace.
Value is location of the file.
__DIR__ sets relative directory.
*/

F::loadClasses([
    'jonathannicol\\simpleseo\\pagemeta' => 'src/PageMeta.php'
], __DIR__);

/* Plugin definition */

Kirby::plugin(
    name: 'jonathannicol/simple-seo',
    extends: [
        'options' => [
            'appendSiteTitle' => true,
            'titleSeparator'  => '-',
            'sitemapExclude' => ['error'],
            'social' => true,
            'schema' => true,
            'schemaLocale' => '',
            'schemaExtra' => [],
            'robots' => true,
            'robotsIndex' => true,
            'robotsFollow' => true,
            'robotsArchive' => true,
            'robotsImageIndex' => true,
            'autoImages' => [],
        ],
        'pageMethods' => [
            'meta' => function () {
                return new PageMeta($this); // $this is the current page
            },
        ],
        // Set up snippet routes for Kirby to find the snippet files in this plugin
        'snippets' => [
            'seo/head' => __DIR__ . '/snippets/head.php',
            'seo/sitemap' => __DIR__ . '/snippets/sitemap.php',
        ],
        // Set up blueprint routes for Kirby to find the blueprint files in this plugin
        'blueprints' => [
            'seo/tab/page' => __DIR__ . '/blueprints/tab-page.yml',
            'seo/tab/site' => __DIR__ . '/blueprints/tab-site.yml',
        ],
        'routes' => [
            [
                'pattern' => 'sitemap.xml',
                'action' => function () {
                    // @see https://getkirby.com/docs/cookbook/navigation/sitemap

                    // fetch all pages
                    $pages = site()->pages()->index();

                    /*
                    Fetch the pages to ignore from the plugin's config
                    */
                    $ignore = option('jonathannicol.simple-seo.sitemapExclude', []);

                    $content = snippet('seo/sitemap', compact('pages', 'ignore'), true);

                    // return response with correct header type
                    return new Response($content, 'application/xml');
                }
            ],
            [
                'pattern' => 'sitemap',
                'action'  => function () {
                    // Alias /sitemap to /sitemap.xml
                    return go('sitemap.xml', 301);
                }
            ]
        ],
        'translations' => [
            'en' => require __DIR__ . '/translations/en.php'
        ],

    ]
);
