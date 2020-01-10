<?php
/* Author: Matthew Frankland --------------------- */

require_once("../../../../wp-load.php"); // Load Wordpress Functions

/**
 *
 * [pmdef_set_post_levels Edit Required Membership Of Post In Database By PostID]
 *
 * @param [int] $post_id   [ID of New Article Post]
 *
 * @param [array] $level_ids [Users Selected Levels]
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
        throw new \Exception( 'Invalid $level_ids argument supplied to pmdef_set_post_levels' ); // Level IDs Do Not Exist
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
 * @param  [String] $category [Category Name]
 *
 */
function post_articles( $category ) {
	$i = 0;

	while ( $i < (int) $_POST[ 'article-index' ] ) {
		if ( isset( $_POST[ 'article-title-' . $i ] ) && isset( $_POST[ 'article-body-' . $i ] ) && isset( $_POST[ 'article-date-' . $i ] ) && isset( $_POST[ 'article-time-' . $i ] ) ) {

      date_default_timezone_set( 'GMT' );
      $arr = [ $_POST[ 'article-date-' . $i ], $_POST[ 'article-time-' . $i ] . ":00" ];

  		if( substr(date( "Y-m-d H:i:s", strtotime( "$arr[0] $arr[1]" ) ), 0, 4 ) == '1970' ) {
  			return false;
  		} else {

        /* Create New Article */

  			$new_post = array(
  				'post_title' => $_POST[ 'article-title-' . $i ],
  				'post_content' => $_POST[ 'article-body-' . $i ],
  				'post_date' => date( "Y-m-d H:i:s", strtotime( "$arr[0] $arr[1]" ) ),
  				'post_status' => 'publish',
  				'post_author' => get_current_user_id(),
  				'post_category' => array( get_cat_ID( $category ) )
  			);
  			$post_id = wp_insert_post( $new_post );

        /* Edit Custom Fields */

  			if ( isset( $_POST[ 'category-title-' . $i ] ) ) {
  				update_field( "field_59d7388534ea5", $_POST[ 'category-title-' . $i ], $post_id );
  			}
  			if ( isset( $_POST[ 'category-written-by-' . $i ] ) ) {
  				update_field( "field_59d73cb25eab2", $_POST[ 'category-written-by-' . $i ], $post_id );
  			}
  			if ( isset( $_POST[ 'category-snippet-' . $i ] ) ) {
  				update_field( "field_59d7435a332ea", $_POST[ 'category-snippet-' . $i ], $post_id );
  			}
  			if ( isset( $_POST[ 'category-sport-category-' . $i ] ) ) {
  				update_field( "field_59c570a4afcdc", $_POST[ 'category-sport-category-' . $i ], $post_id );
  			}
  			if ( isset( $_POST[ 'category-home-team-' . $i ] ) ) {
  				update_field( "field_59c570cdafcdd", $_POST[ 'category-home-team-' . $i ], $post_id );
  			}
  			if ( isset( $_POST[ 'category-home-score-' . $i ] ) ) {
  				update_field( "field_59c570dfafcdf", $_POST[ 'category-home-score-' . $i ], $post_id );
  			}
  			if ( isset( $_POST[ 'category-away-team-' . $i ] ) ) {
  				update_field( "field_59c570d9afcde", $_POST[ 'category-away-team-' . $i ], $post_id );
  			}
  			if ( isset( $_POST[ 'category-away-score-' . $i ] ) ) {
  				update_field( "field_59c577e0c0a88", $_POST[ 'category-away-score-' . $i ], $post_id );
  			}

        /* Set Article Subscription Requirements */

  			$user_ids_subscriber = array();
  			if ( !is_null ( $_POST[ 'article-membership-subscriber-' . $i ] ) ) {
  				array_push( $user_ids_subscriber, 1 );
  			}
  			if ( !is_null ( $_POST[ 'article-membership-subscriber-year-' . $i ] ) ) {
  				array_push( $user_ids_subscriber, 2 );
  			}
  			pmdef_set_post_levels ( $post_id, $user_ids_subscriber );

        /* Set Article Featured Image */

  			if ( isset( $_POST[ 'article-image-' . $i ] ) ) {
  				set_post_thumbnail( $post_id, ( ( int ) $_POST[ 'article-image-' . $i ] ) );
  			}

        /* Set Single Post Template */

        if ( $category == "News" ) {
          update_post_meta($post_id, '_wp_post_template', "news-post-template.php");
        } else {
          update_post_meta($post_id, '_wp_post_template', "sport-post-template.php");
        }

      }
		}
		$i++;
	}
}

/**
 *
 * [If Form Submitted GET Slug And Process]
 *
 */
if( isset( $_POST[ 'submit' ] ) )
{
	unset( $_POST[ 'submit' ] );

	$buttons = '<form action="../../../../wp-admin"><button type="submit">Go To WP Admin Home</button></form><br /><form action="../../../../wp-admin/edit.php"><button type="submit">Go To All Posts</button></form>';
	$success = '<h2>Articles Posted!!</h2>' . $buttons;

	if ( $_POST[ 'article-title-0' ] == "" || $_POST[ 'article-body-0' ] == "" ) {
		echo '<h2>No Articles Listed That Could Be Publishing (Missing One Or More Titles, Body, and Publishing Time)</h2>' . $buttons;
	} else {
		if ( $_GET[ 'page' ] == "news-articles" ) {
			if ( ! is_null( post_articles( "News" ) ) ) {
        echo '<h2>Some Publishing Dates & Times Were In An Invalid Format. Affected Articles Automatically Added With The Date Of 1970/01/01</h2>';
      }
      echo $success ;
		} else if ( $_GET[ 'page' ] == 'sports-articles' ) {
			post_articles( "Sport" );
			echo $success ;
		} else {
			echo( '<h2>Error. Previous Page Unrecognised. Try Again!!</h2>' . $buttons );
		}
	}
}

?>
