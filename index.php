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
    name: 'jnicol/simple-seo',
    extends: [
        'options' => [
            'autoImages' => [],
            'appendSiteTitle' => true,
            'canonical' => true,
            'titleSeparator'  => '-',
            'title' => true,
            'meta' => true,
            'robots' => true,
            'robotsIndex' => true,
            'robotsFollow' => true,
            'robotsArchive' => true,
            'robotsImageIndex' => true,
            'sitemap' => false,
            'sitemapExclude' => ['error'],
            'social' => true,
            'schema' => true,
            'schemaLocale' => '',
            'schemaExtra' => [],

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
        'routes' => function (Kirby $kirby) {
            // Routes that are always registered
            $routes = [];

            // Sitemap routes are optional
            if ($kirby->option('jnicol.simple-seo.sitemap', true) === true) {
                $routes[] = [
                    'pattern' => 'sitemap.xml',
                    'action'  => function () {
                        // @see https://getkirby.com/docs/cookbook/navigation/sitemap
                        $pages  = site()->pages()->index();
                        $ignore = option('jnicol.simple-seo.sitemapExclude', []);

                        $content = snippet('seo/sitemap', compact('pages', 'ignore'), true);

                        return new Response($content, 'application/xml');
                    }
                ];

                $routes[] = [
                    'pattern' => 'sitemap',
                    'action'  => function () {
                        // Alias /sitemap to /sitemap.xml
                        return go('sitemap.xml', 301);
                    }
                ];
            }

            return $routes;
        },
        'translations' => [
            'en' => require __DIR__ . '/translations/en.php',
            'de' => require __DIR__ . '/translations/de.php'
        ],

    ]
);
