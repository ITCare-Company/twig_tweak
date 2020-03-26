## SUMMARY

Twig Tweak module provides a Twig extension with some useful functions and
filters.

## Usage

### Drupal View
```twig
{{ drupal_view('who_s_new', 'block_1') }}
```

### Drupal View Result
```twig
{{ drupal_view('who_s_new', 'block_1') }}
```

### Drupal Block
In order to list all registered plugin IDs fetch them with block plugin manager.
With Drush it can be done like follows:
```
drush ev "print_r(array_keys(\Drupal::service('plugin.manager.block')->getDefinitions()));"
```

```twig
{# Print block using default configuration. #}
{{ drupal_block('system_branding_block') }}

{# Print block using custom configuration. #}
{{ drupal_block('system_branding_block', {label: 'Branding', use_site_name: false})

{# Bypass block.html.twig theming. #}
{{ drupal_block('system_branding_block', wrapper=false) }}
```

@see https://www.drupal.org/node/2964457#block-plugin

### Drupal Region

```twig
{# Print 'Sidebar First' region of the default site theme. #}
{{ drupal_region('sidebar_first') }}

{# Print 'Sidebar First' region of Bartik theme. #}
{{ drupal_region('sidebar_first', 'bartik') }}
```

### Drupal Entity
```twig
{# Print a content block which ID is 1. #}
{{ drupal_entity('block_content', 1) }}

{# Print a node's teaser. #}
{{ drupal_entity('node', 123, 'teaser') }}

{# Print Branding block which was previously disabled on #}
{# admin/structure/block page. #}
{{ drupal_entity('block', 'bartik_branding', check_access=false) }}
```

### Drupal Entity Form
```twig
{# Print edit form for node 1. #}
{{ drupal_entity_form('node', 1) }}

{# Print add form for Article content type. #}
{{ drupal_entity_form('node', values={type: 'article'}) }}

{# Print user register form. #}
{{ drupal_entity_form('user', NULL, 'register', check_access=false) }}
```

### Drupal Field
```twig
{{ drupal_field('field_image', 'node', 1) }}
{{ drupal_field('field_image', 'node', 1, 'teaser') }}
{{ drupal_field('field_image', 'node', 1, {type: 'image_url', settings: {image_style: 'large'}}) }}
```

### Drupal Menu
```twig
{{ drupal_menu('main') }}
```

### Drupal Form
```twig
{{ drupal_form('Drupal\\search\\Form\\SearchBlockForm') }}
```

### Drupal Image

```twig
{# Render image specified by file ID. #}
{{ drupal_image(123) }}

{# Render image specified by file UUID. #}
{{ drupal_image('9bb27144-e6b2-4847-bd24-adcc59613ec0') }}

{# Render image specified by file URI. #}
{{ drupal_image('public://ocean.jpg') }}

{# Render image using 'thumbnail' image style and custom attributes. #}
{{ drupal_image('public://ocean.jpg', 'thumbnail', {alt: 'The alternative text'|t, title: 'The title text'|t}) }}

{# Render responsive image. #}
{{ drupal_image('public://ocean.jpg', 'wide', responsive=true) }}
```


### Drupal Token
```twig
{{ drupal_token('site:name') }}
```

### Drupal Config
```twig
{{ drupal_config('system.site', 'name') }}
```

### Drupal Dump
```twig
{# Basic usage. #}
{{ drupal_dump(var) }}

{# Same as above but shorter. #}
{{ dd(var) }}

{# Dump all available variables for the current template. #}
{{ dd() }}
```

### Drupal Title
```twig
{{ drupal_title() }}
```

### Drupal URL
```twig
{# Basic usage. #}
{{ drupal_url('node/1') }}

{# Complex URL. #}
{{ drupal_url('node/1', {query: {foo: 'bar'}, fragment: 'example', absolute: true}) }}
```

### Drupal Link

```twig
{# It supports the same options as drupal_url(), plus attributes. #}
{{ drupal_link('View'|t, 'node/1', {attributes: {target: '_blank'}}) }}

{# This link will only be shown for privileged users. #}
{{ drupal_link('Example'|t, '/admin', check_access=true) }}
```

### Drupal Messages
```twig
{{ drupal_messages() }}
```

### Drupal Breadcrumb
```twig
{{ drupal_breadcrumb() }}
```

### Drupal Breakpoint
```twig
{{ drupal_breakpoint() }}
```

### Contextual Links
```twig
{# Basic usage. #}
<div class="contextual-region">
  {{ contextual_links('entity.view.edit_form:view=frontpage&display_id=feed_1') }}
  {{ drupal_view('frontpage') }}
</div>
{# Multiple links. #}
<div class="contextual-region">
  {{ contextual_links('node:node=123|block_content:block_content=123') }}
  {{ content }}
</div>
```

### Token Replace
```twig
{# Basic usage. #}
{{ '<h1>[site:name]</h1><div>[site:slogan]</div>'|token_replace }}

{# This is more suited to large markup. #}
{% apply token_replace %}
  <h1>[site:name]</h1>
  <div>[site:slogan]</div>
{% endapply %}
```

### Preg Replace
```twig
{{ 'Drupal - community plumbing!'|preg_replace('/(Drupal)/', '<b>$1</b>') }}
```
For simple string interpolation consider using built-in `replace` or `format`
Twig filters.

### Image Style
```twig
{{ 'public://images/ocean.jpg'|image_style('thumbnail') }}
```

### Transliterate
```twig
{{ 'Привет!'|transliterate }}
```
new TwigFilter('transliterate', [self::class, 'transliterateFilter']),

### Check Markup
```twig
{{ '<b>bold</b> <strong>strong</strong>'|check_markup('restricted_html') }}
```

### Truncate
```twig
{{ 'Some long text'|truncate(10, true) }}
```

### View
```twig
{# Do not put this into node.html.twig template to avoid recursion. #}
{{ node|view }}
{{ node|view('teaser') }}

{{ node.field_image|view }}
{{ node.field_image[0]|view }}
{{ node.field_image|view('teaser') }}
{{ node.field_image|view({settings: {image_style: 'thumbnail'}}) }}
```

### With
```twig
{# Set top level value. #}
{{ content.field_image|with('#title', 'Photo'|t) }}

{# Set nested value. #}
{{ content|with(['field_image', '#title'], 'Photo'|t) }}
```

### Children
```twig
<ul>
  {% for tag in content.field_tags|children %}
    <li>{{ tag }}</li>
  {% endfor %}
</ul>
```

### File URL
For string arguments it works similar to core `file_url()` Twig function.
```twig
{{ 'public://sea.jpg'|file_url }}
```

When field item list passed the URL will be extracted from the first item. In
order to get URL of specific item specify its delta explicitly using array
notation.
```twig
{{ node.field_image|file_url }}
{{ node.field_image[0]|file_url }}
```

Media fields are fully supported including OEmbed resources.
```twig
{{ node.field_media|file_url }}
```

### PHP
PHP filter is disabled by default. You can enable it in settings.php file as
follows:
```twug
$settings['twig_tweak_enable_php_filter'] = TRUE;
```

```twig
{{ 'return date('Y');'|php }}
```

Using PHP filter is discouraged as it may cause security implications. In fact
it is very rarely needed. The above code can be replaced with following.
```twig
{{ 'now'|date('Y') }}
```

## LINKS
Project page: https://www.drupal.org/project/twig_tweak
Twig home page: http://twig.sensiolabs.org
Drupal 8 Twig documentation: https://www.drupal.org/docs/8/theming/twig
