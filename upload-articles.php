<?php
/**
* @author Matthew Frankland
**/

require_once( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' ); // Load Wordpress Functions

//require_once("../../../wp-load.php"); // Load Wordpress Functions

/**
 *
 * [pmdef_set_post_levels Edit Required Membership Of Post In Database By PostID]
 *
 * @author Matthew Frankland
 * @param {number} $post_id - ID of New Article Post
 * @param {array} $level_ids - Users Selected Levels
 *
 */
function pmdef_set_post_levels( $post_id, $level_ids )
{
    global $wpdb;
    if ( !isset( $wpdb->pmpro_memberships_pages ) ) { // Check That Valid PMPro Plugin Exists In Database
        throw new \Exception(
            'PMPro is not installed or your version is not supported. ' .
            'Could not find $wpdb->pmpro_memberships_pages.'
        );
    }
    if ( is_numeric( $level_ids ) ) {
        $level_ids = [ intval( $level_ids ) ];
    } elseif ( is_array( $level_ids ) ) {
        foreach ( $level_ids as $level_idx => $level_id ) {
            $level_ids[ $level_idx ] = intval( $level_id );
        }
    } elseif ( is_null( $level_ids ) || $level_ids === false ) {
        $level_ids = [];
    } else {
      return json_encode( array( "error" => "Invalid \$level_ids Argument Supplied To 'pmdef_set_post_levels'" ) ); // Level IDs Do Not Exist
    }
    $post_id = intval( $post_id );
    $existing_level_rows = $wpdb->get_results(
        "SELECT membership_id FROM {$wpdb->pmpro_memberships_pages} WHERE page_id = '$post_id'",
        ARRAY_A
    );
    $existing_levels = [];
    foreach ( $existing_level_rows as $existing_level_row ) {
        $existing_levels[] = intval( $existing_level_row[ 'membership_id' ] );
    }
    if ( $existing_levels === $level_ids ) {
        return false;
    } else {
        $wpdb->query( "DELETE FROM {$wpdb->pmpro_memberships_pages} WHERE page_id = '$post_id'" );
        foreach ($level_ids as $level_id) {
            $wpdb->query(
                "INSERT INTO {$wpdb->pmpro_memberships_pages} (membership_id, page_id) " .
                "VALUES('" . $level_id . "', '" . $post_id . "')"
            );
        }
        return true;
    }
}

/**
 *
 * [post_articles Cycle Through All Posts in $_POSTS And Schedule]
 * 
 * @author Matthew Frankland
 * @param {String} $category - Category Name
 *
 */
function post_articles( $category ) {

  $index = 1;
  foreach ( $_POST[ "article-index" ] as $i ) {
    if ( ( int ) $i > $index ) {
      $index = ( int ) $i;
    }
  }

	for ($i = 1; $i <= $index; $i++) {
		if ( isset( $_POST[ "article-title-" . $i ] ) && isset( $_POST[ "article-body-" . $i ] ) && isset( $_POST[ "date-picker-" . $i ] ) ) {
      $new_post = array(
        "post_title" => $_POST[ "article-title-" . $i ],
        "post_content" => $_POST[ "article-body-" . $i ],
        "post_date" => $_POST[ "date-picker-" . $i ],
        "post_status" => "publish",
        "post_author" => get_current_user_id(),
        "post_category" => array( get_cat_ID( $category ) )
      );
        
      $post_id = wp_insert_post( $new_post );

      if ( is_wp_error( $result ) ){
        return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => $post_id->get_error_message() ) );
      }

      /* Edit Custom Fields */

      if ( isset( $_POST[ "category-title-" . $i ] ) ) {
        if ( ! update_field( "field_59d7388534ea5", $_POST[ "category-title-" . $i ], $post_id ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Error Updating 'category-title'" ) );
        }
      }
      if ( isset( $_POST[ "category-written-by-" . $i ] ) ) {
        if ( ! update_field( "field_59d73cb25eab2", $_POST[ "category-written-by-" . $i ], $post_id ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Error Updating 'category-written-by'" ) );
        }
      }
      if ( isset( $_POST[ "category-snippet-" . $i ] ) ) {
        if ( ! update_field( "field_59d7435a332ea", $_POST[ "category-snippet-" . $i ], $post_id ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Error Updating 'category-snippet'" ) );
        }
      }
      if ( isset( $_POST[ "category-sport-category-" . $i ] ) ) {
        if ( ! update_field( "field_59c570a4afcdc", $_POST[ "category-sport-category-" . $i ], $post_id ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Error Updating 'category-sport-category'" ) );
        }
      }
      if ( isset( $_POST[ "category-home-team-" . $i ] ) ) {
        if ( ! update_field( "field_59c570cdafcdd", $_POST[ "category-home-team-" . $i ], $post_id ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Error Updating 'category-home-team'" ) );
        }
      }
      if ( isset( $_POST[ "category-home-score-" . $i ] ) ) {
        if ( ! update_field( "field_59c570dfafcdf", $_POST[ "category-home-score-" . $i ], $post_id ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Error Updating 'category-home-score'" ) );
        }
      }
      if ( isset( $_POST[ "category-away-team-" . $i ] ) ) {
        if ( ! update_field( "field_59c570d9afcde", $_POST[ "category-away-team-" . $i ], $post_id ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Error Updating 'category-away-team'" ) );
        }
      }
      if ( isset( $_POST[ "category-away-score-" . $i ] ) ) {
        if ( ! update_field( "field_59c577e0c0a88", $_POST[ "category-away-score-" . $i ], $post_id ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Error Updating 'category-away-score'" ) );
        }
      }

      /* Set Article Subscription Requirements */

      $user_ids_subscriber = array();
      if ( !is_null ( $_POST[ "article-membership-subscriber-" . $i ] ) ) {
        array_push( $user_ids_subscriber, 1 );
      }
      if ( !is_null ( $_POST[ "article-membership-subscriber-year-" . $i ] ) ) {
        array_push( $user_ids_subscriber, 2 );
      }
      $membership = pmdef_set_post_levels ( $post_id, $user_ids_subscriber );
      if ( ! is_bool ( $membership ) ) {
        return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => $membership[ "error" ] ) );
      }

      /* Set Article Featured Image */

      if ( isset( $_POST[ 'article-image-' . $i ] ) && $_POST[ 'article-image-' . $i ] != "" ) {
        if ( ! set_post_thumbnail( $post_id, ( ( int ) $_POST[ "article-image-" . $i ] ) ) ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Couldn't Set Featured Image" ) );
        }
      }

      /* Set Single Post Template */
          
      if ( $category == "News" ) {
        if ( ! update_post_meta($post_id, "_wp_post_template", "news-post-template.php") ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Couldn't Set Category To 'News'" ) );
        }
      } else {
        if ( ! update_post_meta($post_id, "_wp_post_template", "sport-post-template.php") ) {
          return json_encode( array( "post_title" => $_POST[ "article-title-" . $i ], "error" => "Couldn't Set Category To 'Sport'" ) );
        }
      }
      
    }
  }
  
  return true;
}

/**
 *
 * [If Form Submitted GET Slug And Process]
 *
 */
if ( $_GET[ 'page' ] == "news-articles" ) {
  $articles = post_articles( "News" );
  if ( is_bool ( $articles ) ) {
    http_response_code( 200 );
    echo "Success!!";
  } else {
  http_response_code( 500 );
    echo json_encode( array( "error" => True, "message" => $articles ) );
  }
} else if ( $_GET[ 'page' ] == 'sports-articles' ) {
  $articles = post_articles( "Sport" );
  if ( is_bool ( $articles ) ) {
    http_response_code( 200 );
    echo "Success!!";
  } else {
  http_response_code( 500 );
    echo json_encode( array( "error" => True, "message" => $articles ) );
  }
} else {
  http_response_code( 500 );
  echo json_encode( array( "error" => True, "message" => "Unkown Category Sent To Article Publisher" ) );
}

?>
