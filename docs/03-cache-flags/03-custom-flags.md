---
title: 'Custom Flags'
post_excerpt: 'Create custom cache flags for your specific needs and learn flag design patterns for efficient cache management.'
menu_order: 30
---

# Custom Flags

While built-in flags cover common cases, custom flags let you target cache clearing based on your specific content structure and business logic.

## Adding Custom Flags

### Via Filter

The `millicache_flags_for_request` filter runs when a page is being cached:

```php
add_filter( 'millicache_flags_for_request', function( $flags ) {
    // Add a flag based on the page template
    if ( is_page_template( 'templates/landing.php' ) ) {
        $flags[] = 'template:landing';
    }

    // Add a flag for pages with a specific Gutenberg block
    if ( is_singular() && has_block( 'my-plugin/hero-banner' ) ) {
        $flags[] = 'block:hero-banner';
    }

    return $flags;
} );
```

### Via PHP Function

Add flags dynamically during template rendering:

```php
// In your theme's template file
if ( is_product() ) {
    millipress_add_flag( 'woo:product' );
    millipress_add_flag( 'woo:product:' . get_the_ID() );
}

// Based on custom logic
if ( get_field( 'show_pricing_table' ) ) {
    millipress_add_flag( 'feature:pricing' );
}
```

### Via Rule Action

Use MilliRules for condition-based flag assignment:

```php
use MilliCache\Deps\MilliRules\Rules;

Rules::create( 'mysite:seasonal-flag' )
    ->on( 'template_redirect', 25 )
    ->when()
        ->has_term( 'seasonal', 'product_cat' )
    ->then()
        ->add_flag( 'promo:seasonal' )
    ->register();
```

Learn more about the powerful [Rules System](../04-rules/01-introduction.md).

## Removing Flags

Sometimes you need to remove a built-in flag:

```php
// Via PHP function
if ( is_front_page() && get_option( 'custom_homepage' ) ) {
    millipress_remove_flag( 'home' );
}
```

```php
// Via rule
Rules::create( 'mysite:no-archive-flag' )
    ->on( 'template_redirect', 30 )
    ->when()
        ->is_post_type_archive( 'product' )
    ->then()
        ->remove_flag( 'archive:post' )
    ->register();
```

## Clearing Cache by Flags

### WP-CLI

```bash
# Clear by specific flag
wp millicache clear --flag="home"

# Clear multiple flags
wp millicache clear --flag="post:123,home,archive:post"

# Clear with wildcard
wp millicache clear --flag="post:*"
wp millicache clear --flag="archive:category:*"

# Multisite: Include site prefix
wp millicache clear --flag="2:post:*"
wp millicache clear --flag="*:home"
```

### PHP Functions

```php
// Clear by flags
millipress_clear_cache_by_flags( ['home', 'archive:post'] );

// Clear with wildcard
millipress_clear_cache_by_flags( 'product:*' );

// Expire instead of delete (serves stale while regenerating)
millipress_clear_cache_by_flags( 'home', true );

// Mixed targets (flags, post IDs, URLs)
millipress_clear_cache( [
    'home',                             // Flag
    'post:123',                         // Flag
    123,                                // Post ID
    'https://example.com/special-page/' // URL
] );
```

## Wildcard Patterns

MilliCache supports wildcards for flexible cache clearing:

### The `*` Wildcard

Matches any number of characters:

| Pattern     | Matches                              |
|-------------|--------------------------------------|
| `post:*`    | `post:1`, `post:123`, `post:999`     |
| `archive:*` | `archive:post`, `archive:category:5` |
| `*:home`    | `1:home`, `2:home` (multisite)       |
| `feature:*` | All feature flags                    |

### The `?` Wildcard

Matches exactly one character:

| Pattern  | Matches                        |
|----------|--------------------------------|
| `post:?` | `post:1` through `post:9` only |
| `?:home` | Sites with single-digit IDs    |

## Flag Design Patterns

### Hierarchical Flags

Use a consistent naming structure for granular control:

```php
// E-commerce example
$flags[] = 'product';                    // All products
$flags[] = 'product:category:5';         // Products in category 5
$flags[] = 'product:5:sku:ABC123';       // Specific product variant

// Clear all products
millipress_clear_cache_by_flags( 'product:*' );

// Clear category only
millipress_clear_cache_by_flags( 'product:category:5' );
```

### Feature Flags

Tag pages by feature for cross-cutting concerns:

```php
add_filter( 'millicache_flags_for_request', function( $flags ) {
    // Tag pages showing dynamic pricing
    if ( has_dynamic_pricing() ) {
        $flags[] = 'feature:dynamic-pricing';
    }

    // Tag pages with real-time inventory
    if ( shows_inventory() ) {
        $flags[] = 'feature:inventory';
    }

    return $flags;
} );

// When pricing engine updates, clear all affected pages
millipress_clear_cache_by_flags( 'feature:dynamic-pricing' );
```

### WooCommerce Integration

```php
add_filter( 'millicache_flags_for_request', function( $flags ) {
    if ( function_exists( 'is_product' ) && is_product() ) {
        $product = wc_get_product();

        // Tag by product type
        $flags[] = 'woo:' . $product->get_type();

        // Tag if on sale
        if ( $product->is_on_sale() ) {
            $flags[] = 'woo:sale';
        }

        // Tag by category
        foreach ( $product->get_category_ids() as $cat_id ) {
            $flags[] = 'woo:cat:' . $cat_id;
        }
    }
    return $flags;
} );

// Clear all sale items when sale ends
millipress_clear_cache_by_flags( 'woo:sale' );
```

### Time-Based Flags

For scheduled cache clearing:

```php
add_filter( 'millicache_flags_for_request', function( $flags ) {
    // Add a date-based flag
    $flags[] = 'date:' . date( 'Y-m-d' );

    // Add a week flag for weekly content
    $flags[] = 'week:' . date( 'Y-W' );

    return $flags;
} );

// Clear yesterday's cached content via cron
$yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
millipress_clear_cache_by_flags( "date:{$yesterday}" );
```

## Best Practices

### Use Descriptive Names

```php
// Good
$flags[] = 'product:featured';
$flags[] = 'archive:sale';

// Avoid
$flags[] = 'x';
$flags[] = '123';
```

### Limit Flag Count

Each flag adds storage overhead. Be selective:

```php
// Good: Few targeted flags
$flags[] = 'post:' . $post->ID;
$flags[] = 'archive:' . $post->post_type;

// Avoid: Excessive flags
foreach ( get_all_meta( $post->ID ) as $key => $value ) {
    $flags[] = "meta:{$key}:{$value}";  // Could be hundreds!
}
```

### Use Wildcards for Clearing

Instead of tracking exact flags, use patterns:

```php
// Good: Use wildcard
millipress_clear_cache_by_flags( 'product:*' );

// Avoid: Listing every flag
millipress_clear_cache_by_flags( ['product:1', 'product:2', 'product:3', ...] );
```

### Document Your Flag Taxonomy

Plan and document your flag structure:

```
Your Site's Flag Structure:
├── home              - Homepage
├── post:{id}         - Individual posts
├── archive:
│   ├── post          - Post archive
│   ├── {type}        - CPT archives
│   └── {tax}:{id}    - Taxonomy archives
├── product:
│   ├── featured      - Featured products
│   └── sale          - On-sale products
├── block:
│   ├── hero          - Pages with hero block
│   └── testimonials  - Pages with testimonials
└── feature:
    ├── pricing       - Dynamic pricing pages
    └── inventory     - Real-time inventory
```

## Next Steps

- [Cache Clearing](../05-usage/20-cache-clearing.md) — All clearing methods
- [Rules Overview](../04-rules/01-introduction.md) — Condition-based caching
- [Hooks & Filters](../07-developers/02-hooks-filters.md) — All flag-related hooks
