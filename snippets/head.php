<?php

use Kirby\Toolkit\Html;

// Call our PageMeta class to get the meta description for this page
$meta = $page->meta();

$meta_title = $meta->title();
$meta_description = $meta->description();
$meta_url = $page->url();
$meta_site_name = $page->site()->title();
$meta_robots = $meta->robots();
$og_image = $meta->ogImage();

/* Page title */

if (option('jnicol.simple-seo.title')):
    echo Html::tag('title', $meta_title) . PHP_EOL;
endif;

/* Meta tags */

if (option('jnicol.simple-seo.canonical')):
    echo '<link rel="canonical" href="' . $meta_url . ' ">'  . PHP_EOL;
endif;

if (option('jnicol.simple-seo.meta')):
    if ($meta_description):
        echo '<meta name="description" content="' . $meta->description() . '">' . PHP_EOL;
    endif;
endif;

if (option('jnicol.simple-seo.robots')):
    echo '<meta name="robots" content="' . $meta_robots . '">' . PHP_EOL;
endif;

/* Open Graph */

if (option('jnicol.simple-seo.social')):
    echo '<meta property="og:title" content="' . $meta_title . '">' . PHP_EOL;
    echo '<meta property="og:type" content="website">' . PHP_EOL;
    echo '<meta property="og:url" content="' . $meta_url . '">' . PHP_EOL;

    if ($meta_description):
        echo '<meta property="og:description" content="' . $meta_description . '">' . PHP_EOL;
    endif;

    echo '<meta property="og:locale" content="en_US">' . PHP_EOL;
    echo '<meta property="og:site_name" content="' . $meta_site_name . '">' . PHP_EOL;

    if ($og_image) {
        echo '<meta property="og:image" content="' . $og_image['url'] . '">' . PHP_EOL;
        echo '<meta property="og:image:width" content="' . $og_image['width'] . '">' . PHP_EOL;
        echo '<meta property="og:image:height" content="' . $og_image['height'] . '">' . PHP_EOL;
        if ($alt = $og_image['alt']) {
            echo '<meta property="og:image:alt" content="' . $alt . '">' . PHP_EOL;
        }
    }
endif;

if (option('jnicol.simple-seo.schema')):
    if (($json = $meta->jsonld()) && empty($json) === false):
        echo '<script type="application/ld+json">' . PHP_EOL;
        echo json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | (option('debug') ? JSON_PRETTY_PRINT : 0)) . PHP_EOL;
        echo '</script>' . PHP_EOL;
    endif;
endif;
