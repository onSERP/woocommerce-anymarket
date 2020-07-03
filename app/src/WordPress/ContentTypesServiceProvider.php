<?php

namespace Anymarket\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register widgets and sidebars.
 */
class ContentTypesServiceProvider implements ServiceProviderInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		// Nothing to register.
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		add_action( 'init', [$this, 'registerPostTypes'] );
		add_action( 'init', [$this, 'registerTaxonomies'] );
	}

	/**
	 * Register post types.
	 *
	 * @return void
	 */
	public function registerPostTypes() {
		// phpcs:disable
		/*
		register_post_type(
			'anymarket_custom_post_type',
			array(
				'labels'              => array(
					'name'               => __( 'Custom Types', 'anymarket' ),
					'singular_name'      => __( 'Custom Type', 'anymarket' ),
					'add_new'            => __( 'Add New', 'anymarket' ),
					'add_new_item'       => __( 'Add new Custom Type', 'anymarket' ),
					'view_item'          => __( 'View Custom Type', 'anymarket' ),
					'edit_item'          => __( 'Edit Custom Type', 'anymarket' ),
					'new_item'           => __( 'New Custom Type', 'anymarket' ),
					'search_items'       => __( 'Search Custom Types', 'anymarket' ),
					'not_found'          => __( 'No custom types found', 'anymarket' ),
					'not_found_in_trash' => __( 'No custom types found in trash', 'anymarket' ),
				),
				'public'              => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'query_var'           => true,
				'menu_icon'           => 'dashicons-admin-post',
				'supports'            => array( 'title', 'editor', 'page-attributes' ),
				'rewrite'             => array(
					'slug'       => 'custom-post-type',
					'with_front' => false,
				),
			)
		);
		*/
		// phpcs:enable
	}

	/**
	 * Register taxonomies.
	 *
	 * @return void
	 */
	public function registerTaxonomies() {
		// phpcs:disable
		/*
		register_taxonomy(
			'anymarket_custom_taxonomy',
			array( 'post_type' ),
			array(
				'labels'            => array(
					'name'              => __( 'Custom Taxonomies', 'anymarket' ),
					'singular_name'     => __( 'Custom Taxonomy', 'anymarket' ),
					'search_items'      => __( 'Search Custom Taxonomies', 'anymarket' ),
					'all_items'         => __( 'All Custom Taxonomies', 'anymarket' ),
					'parent_item'       => __( 'Parent Custom Taxonomy', 'anymarket' ),
					'parent_item_colon' => __( 'Parent Custom Taxonomy:', 'anymarket' ),
					'view_item'         => __( 'View Custom Taxonomy', 'anymarket' ),
					'edit_item'         => __( 'Edit Custom Taxonomy', 'anymarket' ),
					'update_item'       => __( 'Update Custom Taxonomy', 'anymarket' ),
					'add_new_item'      => __( 'Add New Custom Taxonomy', 'anymarket' ),
					'new_item_name'     => __( 'New Custom Taxonomy Name', 'anymarket' ),
					'menu_name'         => __( 'Custom Taxonomies', 'anymarket' ),
				),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'custom-taxonomy' ),
			)
		);
		*/
		// phpcs:enable
	}
}
