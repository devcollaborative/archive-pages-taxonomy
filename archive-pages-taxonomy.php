<?php
/**
 * Plugin Name: Set Archive Pages - Taxonomy
 * Plugin URI: https://github.com/devcollaborative/archive-pages-taxonomy
 * Description: Select a page to override taxonomy archive page title & description.
 * Author: DevCollaborative
 * Author URI: https://devcollaborative.com/
 * Version: 1.0.2
 * Update URI: https://github.com/devcollaborative/archive-pages-taxonomy/releases/latest/
 * */

defined( 'ABSPATH' ) or exit;

define( 'ARCHIVE_PAGES_TAXONOMY_VERSION', '1.0.2' );

/**
 * Plugin updates via GitHub
 */

require dirname( __FILE__ ) . '/updater/plugin-update-checker.php';

$myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/devcollaborative/archive-pages-taxonomy/',
	__FILE__,
	'archive-pages-taxonomy'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');	


/**
 * Add plugin settings page.
 */
function archive_pages_add_settings_page() {
    add_options_page( 'Archive Pages - taxonomy', 'Archive Pages', 'edit_posts', 'archive-pages', 'archive_pages_render_settings_page' );
}
add_action( 'admin_menu', 'archive_pages_add_settings_page' );

/**
 * Render plugin settings page.
 */
function archive_pages_render_settings_page() {
  ?>

	<div class="wrap">
		<h1><?php echo get_admin_page_title(); ?></h1>
		<form action="options.php" method="post">
				<?php
					settings_fields( 'archive_pages_settings' );
					do_settings_sections( 'archive-pages' );
				?>
				<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
		</form>
	</div>

	<?php
}

/**
 * Register plugin setting options.
 */
function archive_pages_settings_init() {
	// Get custom post types that have public archive pages.
	$post_types = get_post_types(array(
		'has_archive' => true,
		'_builtin' 		=> false,
	), 'objects');

	/**
	 * Register a section for our settings page.
	 * Title & callback are blank because we don't want a section heading to show, but settings need a section to appear.
	 */
	add_settings_section('archive_pages_section', '', '', 'archive-pages');


	/* get list of all taxonomies on site that are used in Admin Panel menu
	* includes built_in category, post_tag; excludes post_format
	* includes custom taxonomies
	**/
	$our_taxonomies = get_taxonomies( 
		array(
			'public' => true,
			'show_in_menu' => true
		));

	
	//get terms
	foreach ($our_taxonomies as $taxonomy ){

		//get some settings
		$tax_obj = get_taxonomy( $taxonomy );

		$section_heading = $tax_obj->labels->name. ' Archives';
		$section_name = $taxonomy.'_archive_pages_section';

		//get all the terms
		$terms = get_terms(array( 
				'taxonomy' 		=> $taxonomy, 
				'order_by' 		=> 'name', 
				'order'				=> 'ASC',
				'hide_empty' 	=> false, 
 		));

		//each taxonomy gets a section
		add_settings_section($section_name, $section_heading, '', 'archive-pages');


		//display this if checkbox is checked
		archive_pages_settings_fields( $terms, 'name', $section_name);

	}//end foreach

}
add_action( 'admin_init', 'archive_pages_settings_init' );


/**
 * Render list of all pages.
 *
 * @param array Args passed by add_settings_field function
 */
function archive_pages_wp_dropdown_pages($args) {
	$setting = get_option($args['name']) ?: '';

	wp_dropdown_pages(array(
		'name' 						 => $args['name'],
		'selected' 				 => $setting,
		'show_option_none' => '-- None --',
	));
}

function archive_pages_filter_archive_titles($args) {
	$setting = get_option($args['name']) ?: '';

	wp_dropdown_pages(array(
		'name' 						 => $args['name'],
		'selected' 				 => $setting,
		'show_option_none' => '-- None --',
	));
}



/**
 * Loop through list of archives and output nice label
 *
 * @param $archives array - list of terms
 * @param $label string - array key to use for label
 * @param $section string - section to add fields to
 * 
 */
function archive_pages_settings_fields( $archives, $label, $section ){

	// Add setting & dropdown for each archive
	foreach ($archives as $archive) {

		//get a standard slug for the archive name
		$field_slug = sanitize_title($archive->name);

		// Register setting for this archive.
		register_setting('archive_pages_settings', 'archive_page_' . $field_slug);

		// Add field for this archive.
		add_settings_field(
			'archive_page_' . $field_slug, 			//slug-name to identify field
			$archive->$label, 									//title or label of field
			'archive_pages_wp_dropdown_pages', 	//callback
			'archive-pages',										//settings page
			$section, 													//section of settings page
			array( 'name' => 'archive_page_' . $field_slug )
		);
	}
}

/**
 * Replace link term. Also works with 
 * @link https://developer.wordpress.org/reference/functions/get_term_link/
 * 
 * **/
function archive_pages_replace_term_link( $termlink, $term, $taxonomy ){

	$term_slug = $term->slug;
	
	$archive_page_id = get_option('archive_page_' . $term_slug );

	//swap out term URL for the specific assigned landing page URL  
	if ( ( !empty( $archive_page_id ) ) &&
		 ( 'publish' === get_post_status( $archive_page_id ) )
			){
			$termlink = get_page_link( $archive_page_id );
	}

	return $termlink; 

}
add_filter( 'term_link', 'archive_pages_replace_term_link', 10, 3 );

/**
 * Flush rewrite rules when one of the options on the settings page is set or updated
 * */
function archive_pages_flush_rewrites( $option_name, $old_value, $value ){
	
 	if ( str_starts_with( $option_name, 'archive_page_') ){		
 		flush_rewrite_rules(); 
 	}

}
add_action( 'updated_option', 'archive_pages_flush_rewrites', 10, 3 );