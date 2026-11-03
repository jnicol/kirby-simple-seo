<?php

namespace JonathanNicol\SimpleSeo;

use Kirby\Cms\Site;
use Kirby\Cms\Page;

/**
 * Fetch meta data about our page, for use in SEO meta tags
 */
class PageMeta
{

    /**
     * References to Kirby site and current page
     */
    private Site $site;
    private Page $page;

    /**
     * Create new class instance
     * 
     * Called when using the plugin's $page->meta() page method in 
     * snippets/seo-head
     * 
     * @param Page $page a Kirby page
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
        $this->site = $page->site();
    }

    /**
     * Get full page title 
     * 
     * Hierarchy:
     * 
     * 1. Page metaTitle field
     * 2. Site fallback metaTitle field
     * 3. Auto generated
     * 
     * @return string The page title
     */
    public function title(): string
    {
        // 1. Fetch the separator from the plugin's config
        $separator = option('jonathannicol.simple-seo.titleSeparator', '');

        // metaTitle field from this page
        $metaTitle = $this->page->metaTitle();

        // Fallback: field from this site
        if ($metaTitle->isEmpty() && $this->site->metaTitle()->isNotEmpty()) {
            $metaTitle =  $this->site->metaTitle();
        }

        // Fallback: default page title
        if ($metaTitle->isEmpty()) {
            $metaTitle = $this->page->title();
            if (option('jonathannicol.simple-seo.appendSiteTitle')) {
                $metaTitle .= ' ' . $separator . ' ' . $this->site->title();
            }
        }


        // Replace placeholders in the metaTitle
        $metaTitle = str_replace('{{page-title}}', $this->page->title(), $metaTitle);
        $metaTitle = str_replace('{{sep}}', $separator, $metaTitle);
        $metaTitle = str_replace('{{site-title}}', $this->site->title(), $metaTitle);

        return $metaTitle;
    }

    /**
     * Get meta description 
     * 
     * Hierarchy:
     * 
     * 1. Page metaDescription field
     * 2. Page text field
     * 
     * @return string The meta description
     */
    public function description(): string
    {
        // metaDescription field from this page
        $metaDescription = $this->page->metaDescription();

        // Fallback: Text field from this page
        if ($metaDescription->isEmpty() && $this->page->text()->isNotEmpty()) {
            $metaDescription = $this->page->text()->excerpt(160, true, '');
        }

        // Fallback: empty string
        if ($metaDescription->isEmpty()) {
            $metaDescription = '';
        }

        return $metaDescription;
    }

    /**
     * Get open graph image
     * 
     * Hierachy:
     * 
     * 1. Page ogImage field
     * 2. Image field from plugin's autoImages config
     * 3. Site ogImage field
     * 
     * @return array Image url, width, height and alt
     */
    public function ogImage(): ?array
    {
        $image = null;

        // ogImage field from this page
        if ($this->page->ogImage()->isNotEmpty()) {
            $image = $this->page->ogImage()->first()->toFile();
        }

        // Fallback: ogImage from plugin's autoImages config
        if (!$image) {
            $autoImages = option('jonathannicol.simple-seo.autoImages');
            foreach ($autoImages as $field) {
                if ($this->page->content()->get($field)->isNotEmpty()) {
                    $image = $this->page->content()->get($field)->first()->toFile();
                }
            }
        }

        // Fallback: ogImage field from the site
        if (!$image && $this->site->ogImage()->isNotEmpty()) {
            $image = $this->site->ogImage()->first()->toFile();
        }

        // Bail if we couldn't find an image
        if (!$image) {
            return null;
        }

        $image_props = [];

        $image_props['url'] = $image->url();
        $image_props['width'] = $image->width();
        $image_props['height'] = $image->height();
        $image_props['alt'] = $image->alt()->isNotEmpty() ? $image->alt()->value() : '';

        return $image_props;
    }

    /**
     * Get robots rules for this page
     *
     * @return string Directives for robots meta tag
     */
    public function robots(): string
    {
        // Defaults from plugin's config
        // true = allow
        // false = disallow
        $index = option('jonathannicol.simple-seo.robotsIndex');
        $follow = option('jonathannicol.simple-seo.robotsFollow');
        $archive = option('jonathannicol.simple-seo.robotsArchive');
        $imageIndex = option('jonathannicol.simple-seo.robotsImageIndex');

        // Always disable indexing for draft and error pages
        if ($this->page->isDraft() || $this->page->isErrorPage()) {
            $index = false;
        }

        // Override: from page. Toggle field on = disallow
        if ($this->page->robotsIndex()->toBool()) {
            $index = false;
        }
        if ($this->page->robotsFollow()->toBool()) {
            $follow = false;
        }
        if ($this->page->robotsArchive()->toBool()) {
            $archive = false;
        }
        if ($this->page->robotsImages()->toBool()) {
            $imageIndex = false;
        }

        $robots = [
            $index ? 'index' : 'noindex',
            $follow ? 'follow' : 'nofollow',
            $archive ? 'archive' : 'noarchive',
            $imageIndex ? 'imageindex' : 'noimageindex',
        ];

        return implode(', ', $robots);
    }

    /**
     * Generate default field label for robot toggle fields
     * 
     * Makes it clear what the default config value is when the toggle is
     * in the "off" state e.g. "Default: Allow"
     * 
     * @see blueprints/tab-page.yml
     * @param string $option The config option to check against
     * @return string The field label
     */
    public function robotDefaultLabel(string $option): string
    {
        // Display the appropriate label for the plugin config value: "Allow" or "Disallow"
        $value =  option('jonathannicol.simple-seo.' . $option) ? t('jonathannicol.seo.common.allow') : t('jonathannicol.seo.common.disallow');

        return t('jonathannicol.seo.common.default') . ' ' . $value;
    }

    /**
     * Get the JSON-LD data for this page.
     *
     * @return array The JSON-LD data.
     */
    public function jsonld(): array
    {
        $graph = [];

        // WebSite

        $website = [
            '@type' => 'WebSite',
            '@id'   => $this->site->url() . '/#website',
            'url'   => $this->site->url(),
            'name'  => $this->site->title()->toString(),
            'description' => $this->site->siteDescription()->toString(),
        ];

        $graph[] = $website;

        // WebPage

        $webpage = [
            '@type' => 'WebPage',
            '@id'   => $this->page->url() . '/#webpage',
            'url'   => $this->page->url(),
            "name"  => $this->title(),
            "description" => $this->description(),
            "dateModified" => $this->page->modified('Y-m-d\TH:i:sP'),
            "isPartOf" => [
                "@id" => $this->site->url() . '/#website'
            ]
        ];

        $graph[] = $webpage;

        // Locale 
        if ($locale = option('jonathannicol.simple-seo.schemaLocale')) {
            $website['inLanguage'] = $locale;
            $webpage['inLanguage'] = $locale;
        }

        // Merge with extra schema data defined in the plugin's config
        var_dump($graph);
        $graph = array_merge($graph, option('jonathannicol.simple-seo.schemaExtra'));

        $json = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        return $json;
    }
}
