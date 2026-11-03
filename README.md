# Simple SEO Plugin for Kirby CMS

A simple SEO plugin for Kirby CMS. Edit SEO meta fields and Open Graph data from the panel on a per page basis, with a site-wide fallback.

## Install

Copy the contents of this folder to `/site/plugins/kirby-simple-seo`.

## Setup

Include the SEO site tab in your `site.yml` and the SEO page tab in all your page blueprints:

```yaml
# site.yml
tabs:
  seo: seo/tab/site

# e.g. default.yml
tabs:
  seo: seo/tab/page
```

The page tab includes fields for editing per-page metadata, such as `metaTitle`, `metaDescription`, `ogImage` and robots directives.

The site tab adds `siteDescription`, `metaTitle`and `ogImage` fields to your dashboard, which define site-wide metadata and/or serve as a fallback if a page does not have any metadata defined.

## Usage in template

```php
// in <head> of a page template
<?php snippet('seo/head') ?>
```

## Sitemap

A `sitemap.xml` file is automatically generated at the site's root.

Pages can be exluded from the sitemap with the sitemapExclude config setting (see below).

## Options

Options can be set in `/site/config/config.php`:

```php
return [
  'jonathannicol.simple-seo' => [
    'titleSeparator'  => '|',
    'autoImages' => ['cover_image'],
  ]
]
```

<!-- prettier-ignore -->
| Property | Type | Default | Description |
| --- | ---| --- | --- |
| appendSiteTitle | `bool` | `true` | Append site title should be appended to auto-generated page titles. |
| titleSeparator |`string`| `'-'` | A visual separator between the page and site title. |
| sitemapExclude |`array`| `['error']` |An array of page slugs to exclude from the sitemap. |
| social |`bool`| `true` | Generate Open Graph meta tags. |
| schema |`bool`| `true` | Generate JSON-LD schema data. |
| schemaLocale |`string`| `''` | The langage your wesite is written in. Use one of the language codes from the IETF BCP 47 standard. |
| schemaExtra |`array` | `[]` | Additional data to include in the schema @graph object, defained as a PHP array. |
| robots |`bool`| `true` | Generate robots meta tag. |
| robotsIndex |`bool` | `true` | Allows crawlers to index pages. Can be disallowed per-page. |
| robotsFollow |`bool` | `true` | Allows crawlers to follow links on pages. Can be disallowed per-page. |
| robotsArchive |`bool` | `true` | Allows crawlers to serve cached page versions. Can be disallowed per-page. |
| robotsImageIndex |`bool` | `true` | Allows crawlers to include imagesin search results. Can be disallowed per-page. |
| autoImages |`array` | `[]` | Array of fields to use as the Open Graph image if one was not manually defined. The first matching field will be used. |

---

Made by Jonathan Nicol and released under an MIT license.
