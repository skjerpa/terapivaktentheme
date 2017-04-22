<?php

//clean WP HEAD
function head_cleanup() {
  // Originally from http://wpengineer.com/1438/wordpress-header/
  remove_action('wp_head', 'feed_links_extra', 3);
  add_action('wp_head', 'ob_start', 1, 0);
  add_action('wp_head', function () {
    $pattern = '/.*' . preg_quote(esc_url(get_feed_link('comments_' . get_default_feed())), '/') . '.*[\r\n]+/';
    echo preg_replace($pattern, '', ob_get_clean());
  }, 3, 0);
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'wp_shortlink_wp_head', 10);
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('admin_print_scripts', 'print_emoji_detection_script');
  remove_action('wp_print_styles', 'print_emoji_styles');
  remove_action('admin_print_styles', 'print_emoji_styles');
  remove_action('wp_head', 'wp_oembed_add_discovery_links');
  remove_action('wp_head', 'wp_oembed_add_host_js');
  remove_action('wp_head', 'rest_output_link_wp_head', 10);
  remove_filter('the_content_feed', 'wp_staticize_emoji');
  remove_filter('comment_text_rss', 'wp_staticize_emoji');
  remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
  add_filter('use_default_gallery_style', '__return_false');
  add_filter('emoji_svg_url', '__return_false');
  global $wp_widget_factory;
  if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
    remove_action('wp_head', [$wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style']);
  }
}
add_action('init', __NAMESPACE__ . '\\head_cleanup');

if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_author-info',
		'title' => 'Author Info',
		'fields' => array (
			array (
				'key' => 'field_58e019d6552ba',
				'label' => 'Author Title',
				'name' => 'author_title',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'none',
				'maxlength' => '',
			),
			array (
				'key' => 'field_58e03cdc767f5',
				'label' => 'Author Profile Pic',
				'name' => 'author_avatar',
				'type' => 'image',
				'save_format' => 'url',
				'preview_size' => 'sb_cpt_li_150_square',
				'library' => 'all',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'ef_user',
					'operator' => '==',
					'value' => 'all',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}


//remove post formats
// Higher value on the priority then the default of 10 makes sure this is run after the initial removal.
add_action('after_setup_theme', 'remove_post_format', 15);
function remove_post_format() {
    remove_theme_support('post-formats');
}

// Disable support for comments and trackbacks in post types
function df_disable_comments_post_types_support() {
	$post_types = get_post_types();
	foreach ($post_types as $post_type) {
		if(post_type_supports($post_type, 'comments')) {
			remove_post_type_support($post_type, 'comments');
			remove_post_type_support($post_type, 'trackbacks');
		}
	}
}
add_action('admin_init', 'df_disable_comments_post_types_support');
// Close comments on the front-end
function df_disable_comments_status() {
	return false;
}
add_filter('comments_open', 'df_disable_comments_status', 20, 2);
add_filter('pings_open', 'df_disable_comments_status', 20, 2);
// Hide existing comments
function df_disable_comments_hide_existing_comments($comments) {
	$comments = array();
	return $comments;
}
add_filter('comments_array', 'df_disable_comments_hide_existing_comments', 10, 2);
// Remove comments page in menu
function df_disable_comments_admin_menu() {
	remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'df_disable_comments_admin_menu');
// Redirect any user trying to access comments page
function df_disable_comments_admin_menu_redirect() {
	global $pagenow;
	if ($pagenow === 'edit-comments.php') {
		wp_redirect(admin_url()); exit;
	}
}
add_action('admin_init', 'df_disable_comments_admin_menu_redirect');
// Remove comments metabox from dashboard
function df_disable_comments_dashboard() {
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}
add_action('admin_init', 'df_disable_comments_dashboard');
// Remove comments links from admin bar
function df_disable_comments_admin_bar() {
	if (is_admin_bar_showing()) {
		remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
	}
}
add_action('init', 'df_disable_comments_admin_bar');


add_filter( 'et_project_posttype_args', 'mytheme_et_project_posttype_args', 10, 1 );
function mytheme_et_project_posttype_args( $args ) {
	return array_merge( $args, array(
		'public'              => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => false,
		'show_in_nav_menus'   => false,
		'show_ui'             => false
	));
}


add_action( 'wp_enqueue_scripts', 'my_enqueue_assets' );
function my_enqueue_assets() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri().'/css/child_style.min.css' );
}


// Allow SVG uploads

function allow_svgimg_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'allow_svgimg_types');

function shortcode_post_published_date(){


 return get_the_date('j. F Y');


}
add_shortcode( 'post_published', 'shortcode_post_published_date' );

function shortcode_get_author_name(){

 return get_the_author();

}
add_shortcode( 'get_author', 'shortcode_get_author_name' );


function shortcode_get_author_title(){
$author_id = get_the_author_meta('ID');

return get_field('author_title', 'user_'. $author_id );

}
add_shortcode( 'get_author_title', 'shortcode_get_author_title' );

function shortcode_get_author_image(){
$author_id = get_the_author_meta('ID');

return get_field('author_avatar', 'user_'. $author_id );

}
add_shortcode( 'get_author_image', 'shortcode_get_author_image' );


function shortcode_get_author_info() {
$author_id = get_the_author_meta('ID');
$author_name = get_the_author();

ob_start(); ?>

<div class="author-image-container">
  <img class="author-image aligncenter" src="<?php echo get_field('author_avatar', 'user_'. $author_id ); ?>" alt="<?php echo get_the_author(); ?>" />
</div>
<span class="author_title"><?php _e( 'Skrevet av', 'Divi_child' ); ?></span>
<h5 class="author-name"><?php echo get_the_author(); ?></h5>
<p class="author-title"><?php echo get_field('author_title', 'user_'. $author_id ); ?></p>


<?php
$content = ob_get_contents();
ob_end_clean();
return $content;

}

add_shortcode( 'get_author_info', 'shortcode_get_author_info' );




function shortcode_get_post_tags(){
 return the_tags();
}
add_shortcode( 'get_tags', 'shortcode_get_post_tags' );

// Add Shortcode to wrap selected content with Intro
function intro_text_shortcode( $atts , $content = null ) {
	return '<p class="intro">' . $content . '</p>';
}
add_shortcode( 'intro', 'intro_text_shortcode' );



function faq_section_shortcode() {

 if( function_exists('have_rows') ) {

 ob_start();

 if( have_rows('faq_repeater') ): ?>

     <div class='row'>

  <?php while( have_rows('faq_repeater') ): the_row(); ?>
    <div class="span_2 col">
          <h2>
              <?php the_sub_field('faq_title'); ?>
          </h2>
          <p>
              <?php the_sub_field('faq_content'); ?>
          </p>
        </div>

  <?php endwhile;

  echo "</div>";


 endif;

   $content = ob_get_contents();
   ob_end_clean();
   return $content;

  }

}
add_shortcode('faq-entries', 'faq_section_shortcode');

// Register Custom Post Type
function divi_faq_cpt() {

	$labels = array(
		'name'                  => _x( 'FAQs', 'Post Type General Name', 'divi_child' ),
		'singular_name'         => _x( 'FAQ', 'Post Type Singular Name', 'divi_child' ),
		'menu_name'             => __( 'FAQs', 'divi_child' ),
		'name_admin_bar'        => __( 'FAQ', 'divi_child' ),
		'archives'              => __( 'Item Archives', 'divi_child' ),
		'attributes'            => __( 'Item Attributes', 'divi_child' ),
		'parent_item_colon'     => __( 'Parent Item:', 'divi_child' ),
		'all_items'             => __( 'All Items', 'divi_child' ),
		'add_new_item'          => __( 'Add New Item', 'divi_child' ),
		'add_new'               => __( 'Add New', 'divi_child' ),
		'new_item'              => __( 'New Item', 'divi_child' ),
		'edit_item'             => __( 'Edit Item', 'divi_child' ),
		'update_item'           => __( 'Update Item', 'divi_child' ),
		'view_item'             => __( 'View Item', 'divi_child' ),
		'view_items'            => __( 'View Items', 'divi_child' ),
		'search_items'          => __( 'Search Item', 'divi_child' ),
		'not_found'             => __( 'Not found', 'divi_child' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'divi_child' ),
		'featured_image'        => __( 'Featured Image', 'divi_child' ),
		'set_featured_image'    => __( 'Set featured image', 'divi_child' ),
		'remove_featured_image' => __( 'Remove featured image', 'divi_child' ),
		'use_featured_image'    => __( 'Use as featured image', 'divi_child' ),
		'insert_into_item'      => __( 'Insert into item', 'divi_child' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'divi_child' ),
		'items_list'            => __( 'Items list', 'divi_child' ),
		'items_list_navigation' => __( 'Items list navigation', 'divi_child' ),
		'filter_items_list'     => __( 'Filter items list', 'divi_child' ),
	);
	$rewrite = array(
		'slug'                  => 'ofte-stilte-sporsmal',
		'with_front'            => false,
		'pages'                 => false,
		'feeds'                 => false,
	);
	$args = array(
		'label'                 => __( 'FAQ', 'divi_child' ),
		'description'           => __( 'Frequently Asked Questions', 'divi_child' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-format-chat',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'rewrite'               => $rewrite,
		'capability_type'       => 'post',
	);
	register_post_type( 'divi_faq', $args );

}
add_action( 'init', 'divi_faq_cpt', 0 );

// Register Custom Taxonomy
function page_cateogry_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Categories', 'Taxonomy General Name', 'child_theme' ),
		'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'child_theme' ),
		'menu_name'                  => __( 'Category', 'child_theme' ),
		'all_items'                  => __( 'All Categories', 'child_theme' ),
		'parent_item'                => __( 'Parent Category', 'child_theme' ),
		'parent_item_colon'          => __( 'Parent Category:', 'child_theme' ),
		'new_item_name'              => __( 'New Category Name', 'child_theme' ),
		'add_new_item'               => __( 'Add New Category', 'child_theme' ),
		'edit_item'                  => __( 'Edit Category', 'child_theme' ),
		'update_item'                => __( 'Update Category', 'child_theme' ),
		'view_item'                  => __( 'View Category', 'child_theme' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'child_theme' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'child_theme' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'child_theme' ),
		'popular_items'              => __( 'Popular categories', 'child_theme' ),
		'search_items'               => __( 'Search categories', 'child_theme' ),
		'not_found'                  => __( 'Not Found', 'child_theme' ),
		'no_terms'                   => __( 'No Categories', 'child_theme' ),
		'items_list'                 => __( 'Categories list', 'child_theme' ),
		'items_list_navigation'      => __( 'Category list navigation', 'child_theme' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'page_category', array( 'page' ), $args );

}
add_action( 'init', 'page_cateogry_taxonomy', 0 );

?>
