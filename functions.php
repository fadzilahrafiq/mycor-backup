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
    $formatted_date = "";

    foreach ( $active_enroll as $active ) {
      $enroll_id = $active->id;
      $formatted_date = $active->expire_date;
      break;
    }

    ?>
    <script>
      var expireTs = '<?php echo $formatted_date; ?>';

      var enrollValidEl = document.getElementById('enroll-valid');

      var enrollValidText = enrollValidEl.querySelector('.elementor-icon-list-text');

      enrollValidText.innerHTML = "Enrollment is valid until "+convertDate(expireTs);

      function convertDate(tsString) {
        var date = new Date(tsString);

        const monthNames = ["January", "February", "March", "April", "May", "June",
          "July", "August", "September", "October", "November", "December"];
        const monthIndex = date.getMonth(); // Zero-based index

        // Extract day, month name, year, hours, minutes
        const day = date.getDate();
        const year = date.getFullYear();
        const hours = date.getHours(); // 24-hour format
        const minutes = date.getMinutes().toString().padStart(2, '0'); // Add leading zero for single-digit minutes

        // Format AM/PM indicator based on hours
        const amPm = hours >= 12 ? 'pm' : 'am';
        const displayHours = hours % 12 === 0 ? 12 : hours % 12; // Convert to 12-hour format (adjust if needed)

        // Format the date and time in desired format
        const formattedDateTime = `${day} ${monthNames[monthIndex]} ${year} ${displayHours}:${minutes} ${amPm}`;

        return formattedDateTime;
      }
    </script>
    <?php

    //START: CHECK FOR CURRENT ATTEMPTS

    $attempts = get_current_attempt($enroll_id);

    $latest_attempt = null;

    $latest_certificate = null;

    $cert_enrollment = null;

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
      } else {
        ?>
        <script>
          hide_element('enroll-container');
        </script>
        <?php
      }

      foreach ( $attempts as $attempt ) {
        $latest_attempt = $attempt;
        break;
      }

      if ($latest_attempt->result == 1) {

        $latest_certificate = get_active_certificate($latest_attempt->id);
        
        if (!empty($latest_certificate)) {

          $cert_enrollment = get_enrollment_by_id($latest_certificate->enroll_id);

          ?>
          <script>
            hide_element('assessment-button');
          </script>
          <?php
        } else {
          ?>
          <script>
            hide_element('cert-button');
          </script>
          <?php
        }
      } else {
        ?>
        <script>
          hide_element('cert-button');
        </script>
        <?php
      }

      if (!empty($latest_attempt)) {

        $resultText = "No record";

        if ($latest_attempt->result == 1) {
          $resultText = "Success";
        } else {
          $resultText = "Failed";
        }

        if (!empty($latest_certificate) && $latest_certificate->cert_valid == 0 ) {
          $resultText = "Expired";
        }

        $statusText = "No record";

        if (!empty($cert_enrollment)) {

          if ($cert_enrollment->enroll_valid == 1) {
            $statusText = "Active";
          } else {
            $statusText = "Expired";
          }
        } else if (!empty($active_enroll)) {
          $statusText = "Active";
        }

        ?>
        <script>
          window.addEventListener('load', () => {
            var historyNumEl = document.getElementById('history-number');
            var hnText = historyNumEl.querySelectorAll(".elementor-icon-list-text");
            if (hnText.length > 0) {
              hnText[0].innerHTML = "Result: <?php echo $latest_attempt->correct_count;?>/20";
            }

            var historyResEl = document.getElementById('history-result');
            var hrText = historyResEl.querySelectorAll(".elementor-icon-list-text");
            if (hrText.length > 0) {
              hrText[0].innerHTML = "<?php echo $resultText;?>";
            }

            var historyStaEl = document.getElementById('history-status');
            var hsText = historyStaEl.querySelectorAll(".elementor-icon-list-text");
            if (hsText.length > 0) {
              hsText[0].innerHTML = "Enrollment: <?php echo $statusText;?>";
            }
          })
        </script>
        <?php
      }

    } else {
      ?>
      <script>
        hide_element('enroll-container');
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

  if ( count($all_attempts) <= 0 ) {
    ?>
    <script>
      hide_element('history-container');
    </script>
    <?php
  } else {
    // foreach( $all_attempts as $attempt ) {
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

function get_enrollment_by_id($enroll_id) {
  global $wpdb;

  $table_name = 'enrollment'; // Replace with your table name

  $sql = "SELECT *, ";
  $sql = $sql . "(CASE WHEN NOW() BETWEEN enroll_date AND expire_date THEN 1 ELSE 0 END) AS enroll_valid ";
  $sql = $sql . "FROM $table_name WHERE id = %s";

  $prepared_sql = $wpdb->prepare( $sql, $enroll_id );

  $results = $wpdb->get_results( $prepared_sql );

  if (count($results) <= 0) {
    return [];
  }

  return $results[0];
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
  $sql = $sql . " ORDER BY ah.id DESC";

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

  $user_id = get_current_user_id();

  ?>
  <script>
    window.addEventListener( 'load', (event) => {
      var formElArr = document.querySelectorAll('input.ays_finish');

      for (let i = 0; i < formElArr.length; i++) {
        var formEl = formElArr[i];
        formEl.addEventListener( 'click', function(event) {
          var userId = '<?php echo $user_id; ?>';

          var currentUrl = window.location.href;

          window.open(currentUrl+"/assessment-processing?userid="+userId);
        })
      }
    });
  </script>
  <?php
}

add_action( 'wp_footer', 'assessment_processing' );

function assessment_processing() {
  if ( !is_page( 'assessment-processing' ) ) {
    return;
  }

  $user_id = $_GET['userid'];

  echo $user_id;

  global $wpdb;

  $table_name = $wpdb->prefix . "aysquiz_reports";

  $sql = "SELECT * FROM ".$table_name;
  $sql = $sql . " WHERE user_id = %s ORDER BY id DESC LIMIT 1";

  $prepared_sql = $wpdb->prepare( $sql, $user_id );

  $retrieved_data = $wpdb->get_results( $prepared_sql );

  if ( count( $retrieved_data ) > 0) {
    foreach ( $retrieved_data as $row ) {
      $result_id = $row->id;

      $isExist = verify_assessment_data($result_id);

      if ($isExist == 0) {
        add_assessment_history($row, $user_id);
      }
    }
  }

  echo "<script>window.close()</script>";
}

function verify_assessment_data($result_id) {
  global $wpdb;

  $table_name = "attempt_history";

  $sql = "SELECT * FROM ".$table_name;
  $sql = $sql . " WHERE result_id = %s";

  $prepared_sql = $wpdb->prepare( $sql, $result_id );

  $retrieved_data = $wpdb->get_results( $prepared_sql );

  $isExist = 0;

  if (count($retrieved_data) > 0) {
    $isExist = 1;
  }

  return $isExist;
}

function add_assessment_history($result_obj, $user_id) {
  global $wpdb;

  $table_name = 'enrollment'; // Replace with your table name

  $sql = "SELECT * FROM $table_name WHERE user_id = %s";
  $sql = $sql . " AND NOW() BETWEEN enroll_date AND expire_date";
  $sql = $sql . " ORDER BY id DESC LIMIT 1";

  $prepared_sql = $wpdb->prepare( $sql, $user_id );

  $results = $wpdb->get_results( $prepared_sql );

  if (count($results) > 0) {
    foreach ($results as $row) {
      $enroll_id = $row->id;

      $table_name_2 = "attempt_history";

      $isPass = 0;
      if ($result_obj->corrects_count >= 10) {
        $isPass = 1;
      }

      $current_date = gmdate('Y-m-d');

      $data = array(
        'enroll_id' => $enroll_id,
        'attempt_date' => $current_date,
        'question_count' => $result_obj->questions_count,
        'correct_count' => $result_obj->corrects_count,
        'result' => $isPass,
        'result_id' => $result_obj->id,
      );

      // $sql_2 = "INSERT INTO ".$table_name_2;
      // $sql_2 = $sql_2 . " (enroll_id, attempt_date, question_count, correct_count, result, result_id)";
      // $sql_2 = $sql_2 . " VALUES (%s, NOW(), %s, %s, %s, %s)";


      // $prepared_sql_2 = $wpdb->prepare( $sql_2, $enroll_id, $result_obj->questions_count, $result_obj->corrects_count, $isPass, $result_obj->id );

      // $wpdb->query( $prepared_sql_2 );

      $inserted = $wpdb->insert( $table_name_2, $data );

      if ( $inserted !== false ) {
        $inserted_id = $wpdb->insert_id;

        if ($result_obj->corrects_count >= 10) {
          create_certification($inserted_id, $user_id, $enroll_id);
        }
      } 
    }
  }
}

function create_certification($assessment_id, $user_id, $enroll_id) {
  global $wpdb;

  $table_name = "complete_certificate";

  $sql = "INSERT INTO ".$table_name;
  $sql = $sql . " (enroll_id, attempt_id, completion_date, valid_date, expiry_date)";
  $sql = $sql . " VALUES (%s, %s, NOW(), NOW(), NOW() + INTERVAL 1 YEAR )";

  $prepared_sql = $wpdb->prepare( $sql, $enroll_id, $assessment_id );

  $wpdb->query( $prepared_sql );
}

function get_active_certificate($attempt_id) {
  global $wpdb;

  $table_name = 'complete_certificate'; // Replace with your table name

  $sql = "SELECT *, ";
  $sql = $sql . "(CASE WHEN NOW() BETWEEN valid_date AND expiry_date THEN 1 ELSE 0 END) AS cert_valid ";
  $sql = $sql . "FROM $table_name WHERE attempt_id = %s";

  $prepared_sql = $wpdb->prepare( $sql, $attempt_id );

  $results = $wpdb->get_results( $prepared_sql );

  if (count($results) <= 0) {
    return [];
  }

  return $results[0];
}

add_action( 'wp_footer', 'listen_to_certificate' );

function listen_to_certificate() {
  if ( !is_page( 'course' ) ) {
    return;
  }

  $user_id = get_current_user_id();

  ?>
  <script>
    window.addEventListener("load", () => {
     
      var certificateBtnEl = document.getElementById('cert-button');

      certificateBtnEl.addEventListener("click", () => {
        var userId = '<?php echo $user_id; ?>';

        var currentUrl = window.location.href;

        window.open(currentUrl+"/certificate-download?userid="+userId);
      });

    });
  </script>
  <?php
}

add_action( 'wp_footer', 'perform_certificate_download' );

function perform_certificate_download() {
  if ( !is_page( 'certificate-download' ) ) {
    return;
  }

  $user_id = $_GET['userid'];

  global $wpdb;

  // $table_name = 'complete_certificate'; // Replace with your table name
  $table_name = $wpdb->prefix . "users";
  $table_meta = $wpdb->prefix . "usermeta";

  $sql = "SELECT cc.valid_date valid_date, uu.display_name display_name, um.meta_value passport_value ";
  $sql = $sql . "FROM complete_certificate cc INNER JOIN enrollment ee ON cc.enroll_id = ee.id ";
  $sql = $sql . "INNER JOIN $table_name uu ON ee.user_id = uu.ID ";
  $sql = $sql . "INNER JOIN (SELECT * FROM $table_meta WHERE meta_key = 'passport') um ON uu.ID = um.user_id ";
  $sql = $sql . "WHERE ee.user_id = %s ";
  $sql = $sql . "AND NOW() BETWEEN cc.valid_date AND cc.expiry_date ";
  $sql = $sql . "ORDER BY cc.id DESC LIMIT 1";

  $prepared_sql = $wpdb->prepare( $sql, $user_id );

  $results = $wpdb->get_results( $prepared_sql );

  foreach( $results as $row ) {
    $pdf_output = generate_certificate($row->display_name, "(".$row->passport_value.")", "Issued Date: ".$row->vaid_date);

    echo "<script>console.log('".json_encode($pdf_output)."');</script>";

    // Prepare headers for PDF download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=certificate-' . $user_id . '.pdf');
    header('Content-Length: ' . strlen($pdf_output));

    // Send the generated PDF content to the browser for download
    echo $pdf_output;
  }
}

add_action( 'wp_footer', 'update_profile_view' );

function update_profile_view() {
  if ( !is_page( 'profile' ) ) {
    return;
  }

  $user_id = get_current_user_id();

  update_profile_view_userinfo($user_id);
}

function update_profile_view_userinfo($user_id) {
  global $wpdb;

  $table_name = $wpdb->prefix . "users";
  $table_meta = $wpdb->prefix . "usermeta";

  $sql = "SELECT uu.display_name display_name, uu.user_email user_email, um.meta_value passport_value ";
  $sql = $sql . "FROM $table_name uu ";
  $sql = $sql . "INNER JOIN (SELECT * FROM $table_meta WHERE meta_key = 'passport') um ON uu.ID = um.user_id ";
  $sql = $sql . "WHERE uu.id = %s ORDER BY uu.id DESC LIMIT 1";

  $prepared_sql = $wpdb->prepare( $sql, $user_id );

  $results = $wpdb->get_results( $prepared_sql );

  foreach( $results as $row ) {
    ?>
    <script>
      var email_id = '<?php echo $row->user_email; ?>';
      var passport = '<?php echo $row->passport_value; ?>';

      var emailEl = document.getElementById('profile-email');
      var emailChildEl = emailEl.querySelectorAll('.elementor-icon-list-text');
      if (emailChildEl.length > 0) {
        emailChildEl[0].innerHTML =  "Email: "+email_id;
      }
      // emailEl.innerHTML = "Email: "+email_id;

      var passportEl = document.getElementById('profile-passport');
      var passportChildEl = passportEl.querySelectorAll('.elementor-icon-list-text');
      if (passportChildEl.length > 0) {
        passportChildEl[0].innerHTML =  "Passport: "+passport;
      }
      // passportEl.innerHTML = "Passport: "+passport;
    </script>
    <?php
  }
}