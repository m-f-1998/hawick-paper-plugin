<?php
/**
* Plugin Name: Hawick Paper
* Plugin URI: https://www.thehawickpaper.co.uk/
* Description: Plugin for Uploading News and Sports Articles to The Hawick Paper
* Version: 1.0
* Author: Matthew Frankland
* Author URI: https://www.matthewfrankland.co.uk/
**/

include( $_SERVER["DOCUMENT_ROOT"]."/wp-load.php" ); // Load Wordpress Functions

/**
 *
 * [add_articles Create An Additional Settings Page In Posts]
 *
 * @author Matthew Frankland
 *
 */

add_action( "plugins_loaded", "check_current_user" );
function check_current_user() {
    if ( current_user_can("manage_options") ) { // IF THE CURRENT USER IS AN ADMINISTRATOR
        add_action( "admin_menu", "add_articles" );
        add_theme_support( "post-thumbnails" );
    }
}

function add_articles() {
    add_posts_page( "News Articles", "Upload News", "manage_options", "news-articles", "articles_create_page" );
    add_posts_page( "Sport Articles", "Upload Sports", "manage_options", "sports-articles", "articles_create_page" );
}

/**
 *
 * [articles_create_page Create The Articles Form In The Plugin Page]
 *
 * @author Matthew Frankland
 *
 */

function articles_create_page() {
	wp_enqueue_editor(); // Enqueues The Editor's Styles, Scripts, And Default Settings
    wp_enqueue_media(); // Enqueues All Scripts, Styles, Settings, And Templates Necessary To Use All Media JS APIs
?>
    <!-- [Externals] -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- [Scripts] -->
    <script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) . "date-picker.min.js" ?>"></script>
    <script type="text/javascript" src=<?php echo plugin_dir_url( __FILE__ ) . "plugin-dom.min.js" ?>></script>
    <link rel="stylesheet" type="text/css" href=<?php echo plugin_dir_url( __FILE__ ) . "plugin-style.css" ?>>

    <!-- [Basic DOM] -->
    <h1><?php echo $GLOBALS[ "title" ]; ?></h1>
    <div style="display: none;" id="num-articles"><strong style="padding-right: 10px;">Number of Articles: <span id="quantity">1</span></strong>
    <button style="display: none;" type="button" id="quantity-increase-one">Add an Article</button>
    <button style="display: none;" type="button" id="quantity-increase-all">Add Multiple Articles</button></div>
    <div id="error-success"></div>
    <h2 id="loading" style="color: #387043">Loading Article Tables....</h2>
    <form method="post" id="hp-news-form">
        <input style="display: none;" type="submit" value="Submit" name="submit" id="form-submit"> <strong id="processing-label" style="display:none;">Processing. This may take several minutes...</strong>
    </form>

    <script>
        $( window ).load( () => {
            appendTable( 1, false ); // Create An Initial Article With No Delete Button
            $("#loading").css("display", "none"); // Finished Loading - Show DOM And Hide Loading
            $("#num-articles, #form-submit").css("display", "block");
            $("#quantity-increase-one, #quantity-increase-all").css("display", "inline-block");

            $( "#hp-news-form" ).submit( ( event ) => {
                event.preventDefault();

                if ( <?php $cat = explode( " ", $GLOBALS[ "title" ] ) [ 0 ]; echo is_null( category_exists( $cat ) ) ? 0 : 1; ?> == 0 ) { // Must Have News/Sport Category
                    alert("The Category '" + "<?php echo $cat; ?>" + "' Is Missing. Create Category And Try Again.");
                    return;
                }

                var data = $("#hp-news-form").serialize();
                $("#hp-news-form :input").prop("disabled", true); // Disable New Inputs While Processing
                $("#processing-label").css("display", "block");

                <?php $_POST = array(); ?>
                $.ajax({
                    url : "<?php echo plugin_dir_url( __FILE__ ) . "upload-articles.php?page=" . $_GET[ "page" ] ?>",
                    type: "POST",
                    data: data,
                    success: ( data ) => {
                        $( "#processing-label, #hp-news-form, #num-articles" ).css( "display", "none" );
                        $( "#error-success" ).html(
                            `<i class='fa fa-check-circle fa-2x' style='font-size:48px;color:green'></i>
                            <strong style='padding-left: 10px;'>All Articles Successfully Scheduled</strong>`
                        );
                    },
                    error: ( jXHR ) => {
                        $( "#processing-label" ).css( "display", "none" );
                        var errorMessage = JSON.parse( JSON.parse( jXHR.responseText )[ 'message' ] );
                        $("#error-success").html(
                            `<i class='fa fa-times-circle fa-2x' style='font-size:48px;color:red'></i>
                            <strong style='padding-left: 10px;'>
                                An Error Occured.<br />
                                Error Description: '" + errorMessage[ "error" ] + "'<br />
                                Relating To: '" + errorMessage[ "post_title" ] + "'
                            </strong>`
                        );
                    },
                    complete: () => {
                        $( "#hp-news-form :input" ).prop( "disabled", false );
                        $( window ).scrollTop( 0 );
                    }
                });
            } );
		} );
    </script>
<?php
}