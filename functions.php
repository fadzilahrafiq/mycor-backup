<?php
/**
 *  Functions and definitions for auxin framework
 *
 * 
 * @package    Auxin
 * @author     averta (c) 2014-2024
 * @link       http://averta.net
 */

/*-----------------------------------------------------------------------------------*/
/*  Add your custom functions here -  We recommend you to use "code-snippets" plugin instead
/*  https://wordpress.org/plugins/code-snippets/
/*-----------------------------------------------------------------------------------*/



/*-----------------------------------------------------------------------------------*/
/*  Init theme framework
/*-----------------------------------------------------------------------------------*/
require( 'auxin/auxin-include/auxin.php' );
/*-----------------------------------------------------------------------------------*/

/**
 * CURRENT WORK
 * - REPLACE LATEST ASSESSMENT RESULT IN HISTORY CONTAINER
 */

add_action( 'wp_footer', 'course_page_functions' );

function course_page_functions() {

  if ( !is_page( 'course' ) ) {
    return;
  }
  
  //START: CHECK FOR ACTIVE ENROLLMENT

  ?>
    <script>
      function hide_element(element_id) {
        var el = document.getElementById(element_id);
        if (el) {
          el.style.display = "none";
        }
      }
    </script>
  <?php

  $user_id = get_current_user_id();

  $enroll_id = 0;

  $active_enroll = get_active_enrollment($user_id);

  if ( !empty( $active_enroll ) ) {
    ?>
    <script>
      hide_element('enroll-container');
    </script>
    <?php


    //START: CHECK FOR CURRENT ATTEMPTS

    foreach ( $active_enroll as $active ) {
      $enroll_id = $active->id;
      break;
    }

    $attempts = get_current_attempt($enroll_id);

    $latest_attempt = null;

    $attempt_count = count($attempts);

    ?>
    <script>
      function manipulate_progress_bar(count) {

        const progressBarEl = document.getElementById('attempt-progress');

        const progressEl = progressBarEl.querySelector(".elementor-progress-bar");

        var percentage = count == 0 ? 20 : ((count / 3) * 100);

        // progressEl.style.width = percentage+"%" ;

        const progressText = progressBarEl.querySelector(".elementor-progress-text");

        progressText.innerHTML = "Assessment attempts "+count+"/3";
      }
    </script>
    <?php

    echo "<script>window.addEventListener('load', () => { setTimeout(manipulate_progress_bar(".$attempt_count."), 5000) })</script>";

    if ( !empty( $attempts ) ) {
      
      if (count($attempts) >= 3) {
        ?>
        <script>
          hide_element('assessment-button');
        </script>
        <?php
      }

      foreach ( $attempts as $attempt ) {
        $latest_attempt = $attempt;
        break;
      }

      ?>
      <script>
        var lrnEl = document.getElementById('latest-result-number');
      </script>
      <?php
    }
    //END: CHECK FOR CURRENT ATTEMPTS

  } else {
    ?>
    <script>
      hide_element('registered-container');
    </script>
    <?php
  }

  //END: CHECK FOR ACTIVE ENROLLMENT

  //START: CHECK FOR ALL ENROLLMENT

  $all_attempts = get_all_attempts($user_id);

  if ( empty($all_attempts) ) {
    ?>
    <script>
      hide_element('history-container');
    </script>
    <?php
  }

  //END: CHECK FOR ALL ENROLLMENT
}

function get_active_enrollment($user_id) {
  global $wpdb;

  $table_name = 'enrollment'; // Replace with your table name

  $sql = "SELECT * FROM $table_name WHERE user_id = %s";
  $sql = $sql . " AND NOW() BETWEEN enroll_date AND expire_date";
  $sql = $sql . " ORDER BY id DESC LIMIT 1";

  $prepared_sql = $wpdb->prepare( $sql, $user_id );

  $results = $wpdb->get_results( $prepared_sql );

  return $results;
}

function get_current_attempt($enroll_id) {
  global $wpdb;

  $table_name = 'attempt_history'; // Replace with your table name

  $sql = "SELECT * FROM $table_name WHERE enroll_id = %s";
  $sql = $sql . " ORDER BY id DESC";

  $prepared_sql = $wpdb->prepare( $sql, $enroll_id );

  $results = $wpdb->get_results( $prepared_sql );

  return $results;
}

function get_all_attempts($user_id) {
  global $wpdb;

  $table_name = 'attempt_history'; // Replace with your table name

  $sql = "SELECT * FROM $table_name AS ah INNER JOIN enrollment AS e ON ah.enroll_id = e.id";
  $sql = $sql . " WHERE e.user_id = %s";
  $sql = $sql . " ORDER BY id DESC";

  $prepared_sql = $wpdb->prepare( $sql, $user_id );

  $results = $wpdb->get_results( $prepared_sql );

  return $results;
}

/*
 * Change button text on Product Archives
 */

add_filter( 'woocommerce_loop_add_to_cart_link', 'misha_add_to_cart_text_1' );

function misha_add_to_cart_text_1( $add_to_cart_html ) {
	return str_replace( 'Add to cart', 'Buy now', $add_to_cart_html );
}

/*
 * Change button text on product pages
 */

add_filter( 'woocommerce_product_single_add_to_cart_text', 'misha_add_to_cart_text_2' );

function misha_add_to_cart_text_2( $product ){
	return 'Buy now';
}

/**
 * Redirect to Checkout Page after Add to Cart @ WooCommerce
 */

add_filter( 'woocommerce_add_to_cart_redirect', 'misha_skip_cart_redirect_checkout' );
 
function misha_skip_cart_redirect_checkout( $url ) {
	return wc_get_checkout_url();
}

add_filter( 'woocommerce_product_add_to_cart_url', 'misha_fix_for_individual_products', 10, 2 );
function misha_fix_for_individual_products( $add_to_cart_url, $product ){
 
	if( $product->get_sold_individually() // if individual product
	&& WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product->id ) ) // if in the cart
	&& $product->is_purchasable() // we also need these two conditions
	&& $product->is_in_stock() ) {
		$add_to_cart_url = wc_get_checkout_url();
	}
 
	return $add_to_cart_url;
 
}

add_filter( 'wc_add_to_cart_message_html', 'misha_remove_add_to_cart_message' );
 
function misha_remove_add_to_cart_message( $message ){
	return '';
}

/* WC: Avoid alert message for individual sold product already in cart. */
add_action( 'woocommerce_add_to_cart_validation', 'phlegx_woocommerce_add_to_cart_validation', 11, 2 ); 
function phlegx_woocommerce_add_to_cart_validation( $passed, $product_id ) {
	$product = wc_get_product( $product_id );
	if( $product->get_sold_individually()                                              // if individual product
	&& WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product_id ) ) // if in the cart
	&& $product->is_purchasable()                                                      // if conditions
	&& $product->is_in_stock() ) {
		wp_safe_redirect( wc_get_checkout_url() );
		exit();
    }
    return $passed;
}

/**
 * Added function after a successful payment were made
 */

add_action( 'woocommerce_payment_complete', 'store_order_id_after_payment' );

add_action( 'woocommerce_before_thankyou', 'store_order_id_after_payment' );

function store_order_id_after_payment( $order_id ) {

  $user_id = get_current_user_id();

  global $wpdb;

  $sql = "SELECT * FROM enrollment";
  $sql = $sql . " WHERE user_id = %s AND order_id = %s";

  $prepared_sql = $wpdb->prepare( $sql, $user_id, $order_id );

  $checkExists = $wpdb->get_results( $prepared_sql );

  if (empty($checkExists)) {
    $sql2 = "INSERT INTO enrollment (user_id, order_id, enroll_date, expire_date) VALUES ( %s, %s, NOW(), NOW() + INTERVAL 1 DAY )";

    $prepared_sql2 = $wpdb->prepare( $sql2, $user_id, $order_id );

    $results = $wpdb->query( $prepared_sql2 );
    
    return $results;
  }

  return;
}

add_action( 'wp_footer', 'assessment_load' );

function assessment_load() {
  if ( !is_page( 'asessment' ) ) {
    return;
  }

  ?>
  <script>
    window.onload( (event) => {
      const getResultBtn = document.getElementsByName('ays_finish_quiz');

      getResultBtn.addEventListener( "submit", (obj) => {
        console.log(obj);
      })
    });
  </script>
  <?php
}