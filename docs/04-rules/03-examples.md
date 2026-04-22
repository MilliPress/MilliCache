---
title: 'Caching Rule Examples & Recipes'
post_excerpt: 'Ready-to-use MilliCache rule examples: WooCommerce cart bypass, membership sites, content-type TTL, A/B testing, geolocation, and more using the MilliRules fluent API.'
menu_order: 30
---

# Rule Examples

Practical MilliCache rule examples for common scenarios. All examples use the [MilliRules fluent API](https://millipress.com/docs/millirules/).

## Setup

Add rules early in your plugin or theme:

```php
use MilliCache\Deps\MilliRules\Rules;

// You do not need to wrap them into an add_action() call - rules register themselves
// Instead use ->on() to hook into WordPress actions if needed
```

## Example 1: Different TTL by Content Type

Cache news for 15 minutes, documentation for 1 week:

```php
// Short TTL for news (bootstrap phase - runs before WordPress)
Rules::create( 'mysite:news-ttl', 'php' )
    ->order( 10 )
    ->when()
        ->request_url( '/news/*' )
    ->then()
        ->set_ttl( 900 )  // 15 minutes
    ->register();

// Long TTL for documentation
Rules::create( 'mysite:docs-ttl', 'php' )
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
// Bootstrap phase for early bypass
Rules::create( 'mysite:woo-no-cache', 'php' )
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
Rules::create( 'mysite:members-no-cache', 'wp' )
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
Rules::create( 'mysite:ab-test-flag', 'wp' )
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
Rules::create( 'mysite:no-preview', 'php' )
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
Rules::create( 'mysite:pricing-flag', 'wp' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->is_singular()
        ->has_block( 'acme/pricing-table' )
    ->then()
        ->add_flag( 'block:pricing' )
    ->register();

// Short TTL for pages with live data blocks
Rules::create( 'mysite:live-data-ttl', 'wp' )
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
Rules::create( 'mysite:seasonal-flag', 'wp' )
    ->on( 'template_redirect', 25 )
    ->order( 10 )
    ->when()
        ->is_singular( 'product' )
        ->has_term( 'seasonal', 'product_cat' )
    ->then()
        ->add_flag( 'promo:seasonal' )
    ->register();

// Short TTL for featured content
Rules::create( 'mysite:featured-ttl', 'wp' )
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
Rules::create( 'mysite:breaking-news-ttl', 'wp' )
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
Rules::create( 'mysite:geo-flag', 'php' )
    ->order( 10 )
    ->when()
        ->request_header( 'CF-IPCountry' )  // Cloudflare header
    ->then()
        ->custom_action( function() {
            $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'XX';
            millicache_add_flag( 'geo:' . strtolower( $country ) );
        } )
    ->register();
```

## Example 10: API Rate Limiting Support

Short TTL for API responses:

```php
Rules::create( 'mysite:api-ttl', 'php' )
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
Rules::create( 'mysite:mobile-flag', 'php' )
    ->order( 10 )
    ->when()
        ->custom( 'is-mobile', function() {
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            return preg_match( '/Mobile|Android|iPhone/i', $ua );
        } )
    ->then()
        ->custom_action( function() {
            millicache_add_flag( 'device:mobile' );
        } )
    ->register();

Rules::create( 'mysite:desktop-flag', 'php' )
    ->order( 10 )
    ->when()
        ->custom( 'is-desktop', function() {
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            return ! preg_match( '/Mobile|Android|iPhone/i', $ua );
        } )
    ->then()
        ->custom_action( function() {
            millicache_add_flag( 'device:desktop' );
        } )
    ->register();
```

## Example 12: Clear Cache on External Event

Clear product cache when inventory system updates:

```php
Rules::create( 'mysite:inventory-clear', 'wp' )
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
Rules::create( 'mysite:all-match', 'php' )
    ->when()
        ->request_method( 'GET' )
        ->request_url( '/shop/*' )
        ->cookie( 'currency', 'EUR' )
    ->then()
        // Only runs if ALL conditions match
        ->add_flag( 'shop:eur' )
    ->register();

// OR - any condition can match
Rules::create( 'mysite:any-match', 'php' )
    ->when_any()
        ->request_url( '*/cart/*' )
        ->request_url( '*/checkout/*' )
        ->request_url( '*/account/*' )
    ->then()
        // Runs if ANY condition matches
        ->do_cache( false, 'Dynamic page' )
    ->register();

// NOT - none of the conditions should match
Rules::create( 'mysite:none-match', 'php' )
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
Rules::create( 'mysite:woo-cart-bypass', 'php' )

// Avoid - generic
Rules::create( 'rule1', 'php' )
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

- [MilliRules Documentation](https://millipress.com/docs/millirules/) — Complete rules engine reference
- [Conditions Reference](https://millipress.com/docs/millirules/05-reference/01-conditions/)
- [Actions Reference](https://millipress.com/docs/millirules/05-reference/02-actions/)
- [Cache Flags](../03-cache-flags/01-introduction.md) — Partner feature to rules
