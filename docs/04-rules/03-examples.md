---
title: 'Caching Rule Examples & Recipes'
description: 'Copy-ready MilliCache rule recipes: WooCommerce cart bypass, membership sites, per-content-type TTL, and more, built with the MilliRules fluent API.'
menu_order: 30
---

# Rule Examples

Practical MilliCache rule examples for common scenarios. All examples use the [MilliRules fluent API](https://www.millipress.com/docs/millirules/).

## Setup

Every example below starts from `$rules`, which you get from MilliCache:

```php
$rules = millicache()->rules();
```

### Where to put the code

Anywhere in a plugin or a theme. MilliCache is loaded by then, so the rule registers
right away and no wrapper is needed.

The exception is a file that runs *before* MilliCache, a must-use plugin being the
usual case. On a live request that is still fine, because the page cache loads at the
very top of WordPress. Under WP-CLI it is not: the CLI skips the page cache, so
`millicache()` does not exist yet and the file ends in a fatal error. Register on a
hook there, which also keeps the rules visible to anything reading them outside a
cached request: the abilities API, and `wp millicache rules list` in
[MilliCache Pro](https://www.millipress.com/millicache-pro/).

```php
add_action( 'plugins_loaded', function () {
    millicache()->rules()->create( 'mysite:example' )
        // …
        ->register();
} );
```

### Which phase a rule runs in

You do not have to say. MilliCache reads the conditions and actions you used and
picks the phase that can run them, and a rule that could only have run in the
bootstrap phase is moved to the WordPress phase, because by the time your code runs
that phase is already over. There it still sets the lifetime, adds flags, and decides
whether the generated page is stored.

Use `->on()` only to move a rule to a specific WordPress action.

> **The bootstrap phase cannot be reached from code.** It runs inside
> `advanced-cache.php`, before any plugin, theme or must-use plugin exists, and it
> runs once. Bootstrap rules have to live in the settings, which the drop-in reads
> from the database. Writing them takes
> [MilliCache Pro](https://www.millipress.com/millicache-pro/): either its
> [Rules Builder](https://www.millipress.com/docs/millicache-pro/02-modules/03-rules-builder/)
> or `wp millicache rules import`.
>
> The difference is speed, not capability: a bootstrap rule can turn a request away
> before WordPress loads, while a WordPress-phase rule decides once the page has been
> built.

## Example 1: Different TTL by Content Type

Cache news for 15 minutes, documentation for 1 week:

```php
// Short TTL for news
$rules->create( 'mysite:news-ttl' )
    ->order( 10 )
    ->when()
        ->request_url( '/news/*' )
    ->then()
        ->set_ttl( 900 )  // 15 minutes
    ->register();

// Long TTL for documentation
$rules->create( 'mysite:docs-ttl' )
    ->order( 10 )
    ->when()
        ->request_url( '/docs/*' )
    ->then()
        ->set_ttl( 604800 )  // 1 week
    ->register();
```

## Example 2: WooCommerce Cart/Checkout Bypass

Never cache cart, checkout, or account pages:

```php
// Runs early in the WordPress phase, before the page is stored
$rules->create( 'mysite:woo-no-cache' )
    ->order( 1 )
    ->when_any()
        ->request_url( '*/cart/*' )
        ->request_url( '*/checkout/*' )
        ->request_url( '*/my-account/*' )
        ->cookie( 'woocommerce_*' )
    ->then()
        ->do_cache( false, 'WooCommerce dynamic page' )
    ->register();
```

## Example 3: Membership Site Caching

Cache for guests, bypass for active members:

```php
// WordPress phase - needs user context
$rules->create( 'mysite:members-no-cache' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->is_user_logged_in()
        ->custom( 'is-active-member', function() {
            // Check your membership plugin
            return function_exists( 'hasMembershipLevel' )
                && hasMembershipLevel();
        } )
    ->then()
        ->do_cache( false, 'Active member' )
    ->register();
```

## Example 4: A/B Testing Support

Different cache entries for A/B test variants:

```php
// Add a test variant as a flag
$rules->create( 'mysite:ab-test-flag' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->cookie( 'ab_variant' )
    ->then()
        ->add_flag( 'ab:' . ( $_COOKIE['ab_variant'] ?? 'control' ) )
    ->register();
```

## Example 5: Preview and Draft Bypass

Never cache previews or drafts:

```php
$rules->create( 'mysite:no-preview' )
    ->order( 1 )
    ->when_any()
        ->request_param( 'preview', 'true' )
        ->request_param( 'draft', '1' )
        ->request_param( 'p' )  // Post preview by ID
    ->then()
        ->do_cache( false, 'Preview/draft mode' )
    ->register();
```

## Example 6: Block-Based Rules

Use `has_block()` to target pages containing specific Gutenberg blocks:

```php
// Flag pages with a pricing table block
$rules->create( 'mysite:pricing-flag' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->is_singular()
        ->has_block( 'acme/pricing-table' )
    ->then()
        ->add_flag( 'block:pricing' )
    ->register();

// Short TTL for pages with live data blocks
$rules->create( 'mysite:live-data-ttl' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->is_singular()
        ->has_block( 'acme/live-stock-ticker' )
    ->then()
        ->set_ttl( 60 )  // 1 minute for live data
        ->add_flag( 'live-data' )
    ->register();
```

## Example 7: Term-Based Rules

Use `has_term()` for taxonomy-based caching decisions:

```php
// Flag seasonal products
$rules->create( 'mysite:seasonal-flag' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->is_singular( 'product' )
        ->has_term( 'seasonal', 'product_cat' )
    ->then()
        ->add_flag( 'promo:seasonal' )
    ->register();

// Short TTL for featured content
$rules->create( 'mysite:featured-ttl' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->is_singular( 'post' )
        ->has_term( 'featured', 'post_tag' )
    ->then()
        ->set_ttl( 1800 )  // 30 minutes
        ->add_flag( 'featured' )
    ->register();
```

## Example 8: Conditional TTL by Post Meta

Short TTL for "breaking news" posts:

```php
$rules->create( 'mysite:breaking-news-ttl' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->is_singular( 'post' )
        ->custom( 'is-breaking', function() {
            return get_post_meta( get_the_ID(), 'breaking_news', true );
        } )
    ->then()
        ->set_ttl( 300 )  // 5 minutes for breaking news
        ->add_flag( 'breaking' )
    ->register();
```

## Example 9: Geolocation-Based Caching

Tag cache entries by country for geo-targeted content:

```php
$rules->create( 'mysite:geo-flag' )
    ->order( 10 )
    ->when()
        ->request_header( 'CF-IPCountry' )  // Cloudflare header
    ->then()
        ->custom( 'add-geo-flag', function() {
            $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'XX';
            millicache_add_flag( 'geo:' . strtolower( $country ) );
        } )
    ->register();
```

## Example 10: API Rate Limiting Support

Short TTL for API responses:

```php
$rules->create( 'mysite:api-ttl' )
    ->order( 10 )
    ->when()
        ->request_url( '/api/*' )
        ->request_method( 'GET' )
    ->then()
        ->set_ttl( 60 )      // 1 minute
        ->set_grace( 300 )   // 5 minute grace
    ->register();
```

## Example 11: Mobile vs Desktop Caching

Separate cache entries for mobile and desktop:

```php
$rules->create( 'mysite:mobile-flag' )
    ->order( 10 )
    ->when()
        ->custom( 'is-mobile', function() {
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            return preg_match( '/Mobile|Android|iPhone/i', $ua );
        } )
    ->then()
        ->custom( 'add-mobile-flag', function() {
            millicache_add_flag( 'device:mobile' );
        } )
    ->register();

$rules->create( 'mysite:desktop-flag' )
    ->order( 10 )
    ->when()
        ->custom( 'is-desktop', function() {
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            return ! preg_match( '/Mobile|Android|iPhone/i', $ua );
        } )
    ->then()
        ->custom( 'add-desktop-flag', function() {
            millicache_add_flag( 'device:desktop' );
        } )
    ->register();
```

## Example 12: Clear Cache on External Event

Clear product cache when inventory system updates:

```php
$rules->create( 'mysite:inventory-clear' )
    ->on( 'my_inventory_updated', 10 )  // Your custom hook
    ->when()
        ->custom( 'always', fn() => true )
    ->then()
        ->clear_cache( [ 'product:*', 'woo:sale' ] )
    ->register();
```

## Compound Conditions

Combine conditions with AND, OR, and NOT logic:

```php
// AND (default) - all conditions must match
$rules->create( 'mysite:all-match' )
    ->when()
        ->request_method( 'GET' )
        ->request_url( '/shop/*' )
        ->cookie( 'currency', 'EUR' )
    ->then()
        // Only runs if ALL conditions match
        ->add_flag( 'shop:eur' )
    ->register();

// OR - any condition can match
$rules->create( 'mysite:any-match' )
    ->when_any()
        ->request_url( '*/cart/*' )
        ->request_url( '*/checkout/*' )
        ->request_url( '*/account/*' )
    ->then()
        // Runs if ANY condition matches
        ->do_cache( false, 'Dynamic page' )
    ->register();

// NOT - none of the conditions should match
$rules->create( 'mysite:none-match' )
    ->when_none()
        ->request_method( 'GET' )
        ->request_method( 'HEAD' )
    ->then()
        // Runs if NEITHER GET nor HEAD
        ->do_cache( false, 'Non-cacheable method' )
    ->register();
```

## Tips

### Use Descriptive Rule IDs

```php
// Good - namespace:purpose
$rules->create( 'mysite:woo-cart-bypass' )

// Avoid - generic
$rules->create( 'rule1' )
```

### Choose the Right Phase

| Use Bootstrap (`php`) when... | Use WordPress (`wp`) when... |
|-------------------------------|------------------------------|
| Checking URL patterns | Checking user roles |
| Checking cookies/headers | Checking post meta |
| Setting TTL by path | Checking template |
| Early bypass decisions | Adding content-based flags |

### Keep Bootstrap Rules Simple

Bootstrap rules run before WordPress, so keep them fast:

```php
// Good - simple string match
->when()->request_url( '/api/*' )

// Avoid in bootstrap - complex logic
->when()->custom( 'complex', function() {
    // Loading files, database, etc. defeats the purpose
} )
```

## Learn More

- [MilliRules Documentation](https://www.millipress.com/docs/millirules/) — Complete rules engine reference
- [Conditions Reference](https://www.millipress.com/docs/millirules/05-reference/01-conditions/)
- [Actions Reference](https://www.millipress.com/docs/millirules/05-reference/02-actions/)
- [Cache Flags](../03-cache-flags/01-introduction.md) — Partner feature to rules
- [Visual Rules Builder](https://www.millipress.com/docs/millicache-pro/02-modules/03-rules-builder/) — Build rules like these without code, in [MilliCache Pro](https://www.millipress.com/millicache-pro/)
