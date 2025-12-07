<?php
/**
 * Plugin Name: Test Post Types
 * Description: Registers custom post types and taxonomies for E2E testing
 */

add_action('init', function () {
    // Register 'genre' taxonomy first (before CPT that uses it)
    register_taxonomy('genre', 'book', [
        'label'             => 'Genres',
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'genre'],
    ]);

    // Register 'book' custom post type
    register_post_type('book', [
        'label'        => 'Books',
        'public'       => true,
        'has_archive'  => true,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'      => ['slug' => 'books'],
        'taxonomies'   => ['genre'],
        'menu_icon'    => 'dashicons-book',
    ]);
});
