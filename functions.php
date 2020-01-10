<?php
/* Author: Matthew Frankland --------------------- */

//CREATE CUSTOM IMAGE SIZES
add_theme_support( 'post-thumbnails' );

function add_image_class($class){
    $class .= ' additional-class img-responsive';
    return $class;
}
add_filter('get_image_tag_class','add_image_class');

function my_bootstrap_theme_scripts() {
wp_register_script( 'bootstrap-js', get_template_directory_uri() . '/js/bootstrap.js', array( 'jquery' ), '3.0.1', true );

wp_enqueue_script( 'bootstrap-js' );

}
add_action( 'wp_enqueue_scripts', 'my_bootstrap_theme_scripts' );

function hawickhighnews_widgets_init() {
  	register_sidebar( array(
  		'name'          => __( 'Homepage'),
      'description' => __( 'This Sidebar will display on the Homepage of the website' ),
  		'id'            => 'sidebar-1',
      'before_widget' => '',
      'after_widget' => '',
      'before_title' => '<h3 class="header-title-box-sidebar">',
      'after_title' => '</h3>',
  	) );

    register_sidebar(array(
    	'id' => 'sidebar-2',
    	'name' => __( 'News Post' ),
    	'description' => __( 'This widget displays beside an individual post on the website' ),
    	'before_widget' => '',
    	'after_widget' => '',
    	'before_title' => '<h3 class="header-title-box">',
    	'after_title' => '</h3>',
    ));

    register_sidebar(array(
      'id' => 'sidebar-3',
      'name' => __( 'Main post sidebar' ),
      'description' => __( 'This widget displays next to any single post on the website' ),
      'before_widget' => '<div class="col-md-4">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="header-title-box">',
      'after_title' => '</h3>',
    ));

}
add_action( 'widgets_init', 'hawickhighnews_widgets_init' );

// Register Custom Navigation Walker
require_once('wp_bootstrap_navwalker.php');

register_nav_menus( array(
    'primary' => __( 'Primary Menu', 'THEMENAME' ),
) );

register_nav_menus( array(
    'primary-right' => __( 'Primary Menu on Right', 'THEMENAME' ),
) );

$args = array(
  'width'         => 1920,
  'height'        => 400,
  'default-image' => get_template_directory_uri() . '/images/header.jpg',
  'uploads'       => true,
);
add_theme_support( 'custom-header', $args );

function jptweak_remove_share() {
    remove_filter( 'the_content', 'sharing_display', 19 );
    remove_filter( 'the_excerpt', 'sharing_display', 19 );
    if ( class_exists( 'Jetpack_Likes' ) ) {
        remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
    }
}

add_action( 'loop_start', 'jptweak_remove_share' );

add_shortcode( 'visitor', 'visitor_check_shortcode' );

function visitor_check_shortcode( $atts, $content = null ) {
   if ( ( !is_user_logged_in() && !is_null( $content ) ) || is_feed() )
    return $content;
  return '';
}

add_shortcode( 'member', 'member_check_shortcode' );

function member_check_shortcode( $atts, $content = null ) {
   if ( is_user_logged_in() && !is_null( $content ) && !is_feed() )
    return $content;
  return '';
}

function my_login_logo_one() {
?>
<style type="text/css">
body.login div#login h1 a {
background-image: url(https://thehawickpaper.co.uk/wp-content/uploads/2017/08/Favicon.jpg);  //Add your own logo image in this url
padding-bottom: 30px;
}
</style>
<?php
} add_action( 'login_enqueue_scripts', 'my_login_logo_one' );

/**
 *
 * [add_articles Create An Additional Settings Page In Posts]
 *
 * @author Matthew Frankland
 *
 */
function add_articles() {
	add_posts_page( 'News Articles', 'Upload News', 'manage_options', 'news-articles', 'hp_news_upload' );
	add_posts_page( 'Sports Articles', 'Upload Sports', 'manage_options', 'sports-articles', 'hp_sports_upload' );
}

if ( current_user_can( 'manage_options' ) ) { // IF THE CURRENT USER IS AN ADMINISTRATOR
	add_action( 'admin_menu', 'add_articles' );
	add_theme_support( 'post-thumbnails' );
}

/**
 *
 * [articles_create_page Create The Form In The Settings Page]
 *
 * @param  [string] $title [Header For Settings Page]
 *
 */
function articles_create_page( $title ) {
	wp_enqueue_editor();
	wp_enqueue_media(); ?>

  <link rel="stylesheet" type="text/css" href=<?php echo get_template_directory_uri() . "/articles-upload/articles-upload-style.css" ?>>
	<script type="text/javascript" src=<?php echo get_template_directory_uri() . "/articles-upload/articles-upload-script.js" ?>></script>

  <?php # TRY TO STOP SAFARI/IE BEING USED FOR DATE PICKER INPUT
  	global $is_safari, $is_IE;
  	if ( ! wp_is_mobile() && ( $is_safari || $is_IE )  ) {
  	    ?><script>alert("Best To Use Chrome For This! Make Sure You Get The Date & Time Format Right. You've Been Warned...");</script><?php
  	}
  ?>

	<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
	<h2><?php echo $title ?></h2><br>
	<form method="post" action=<?php echo get_template_directory_uri() . "/articles-upload/articles-upload-process.php?page=" . $_GET[ 'page' ] ?> id="hp-news-form"></form>

	<script>
		jQuery( document ).ready( function( $ ) {
      var today = new Date();
      var dd = today.getDate();
      var mm = today.getMonth() + 1;
      var yyyy = today.getFullYear();

      if (dd < 10) {
        dd = '0' + dd;
      }
      if (mm < 10) {
        mm = '0' + mm;
      }
      var today = yyyy + '-' + mm + '-' + dd;
      var elements = document.getElementsByClassName("todays-date");
      for ( var n = 0; n < elements.length; ++n ) {
          elements[ n ].value = today;
      }
			$('#hp-news-form').submit( function() {
				if ( <?php $category = ( $_GET[ 'page' ] == 'news-articles' ) ? "News" : "Sport"; echo get_cat_ID( $category ) == 0 ? 0 : 1; ?> == 0 ) {
          alert("The Category '" + "<?php echo $category; ?>" + "' Is Missing. Create Category And Try Again.");
					return false;
				 } else {
          <?php $_POST = array(); ?>
          return true;
				 }
			});
		});
	</script>
<?php
}

/**
 *
 * [hp_news_upload Create Page For Uploading News Articles]
 *
 */
function hp_news_upload() {
	articles_create_page( "Upload News Articles" );
}

/**
 *
 * [hp_sports_upload Create Page For Uploading Sports Articles]
 *
 */
function hp_sports_upload() {
	articles_create_page( "Upload Sports Articles" );
}

?>
