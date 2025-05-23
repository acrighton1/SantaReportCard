<?php

// Include WordPress functions
require_once get_stylesheet_directory() . '/includes/stripe-functions.php';


/**
 * Twenty Twenty-Five Child Theme functions and definitions
 *
 * @package twentytwentyfive-child
 */

// Start a session if not already started
if (!session_id()) {
    session_start();
}


// Enqueue parent and child theme styles
function twentytwentyfive_child_enqueue_styles()
{
    // Enqueue parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Enqueue child theme styles
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));

    // Enqueue login.css on the login page only
    if (is_page('login')) { // Adjust 'login' to your specific login page slug if necessary
        wp_enqueue_style('login-style', get_stylesheet_directory_uri() . '/assets/login.css', array('parent-style'));
    }

    // Enqueue dashboard.css on dashboard page
    if (is_page('dashboard')) { // Adjust 'dashboard' to your specific dashboard page slug if necessary
        wp_enqueue_style('dashboard-style', get_stylesheet_directory_uri() . '/assets/dashboard.css', array('parent-style'));
    }

    // Enqueue report-card-style.css on the report card page
    if (is_page('report-card-view')) { // Adjust 'report-card-view' to match your actual report card page slug
        wp_enqueue_style(
            'report-card-style',
            get_stylesheet_directory_uri() . '/assets/report-card-style.css',
            array('parent-style'),
            filemtime(get_stylesheet_directory() . '/assets/report-card-style.css') // Prevents caching issues
        );
    }

    // Enqueue report-card-style.css on the report card page
    if (is_page('register')) { // Adjust 'report-card-view' to match your actual report card page slug
        wp_enqueue_style(
            'parentregistration',
            get_stylesheet_directory_uri() . '/assets/parentregistration.css',
            array('parent-style'),
            filemtime(get_stylesheet_directory() . '/assets/parentregistration.css') // Prevents caching issues
        );
    }

    // Ensure report-card-style.css is applied for both screen and print
    if (is_page('report-card-view')) {
        wp_enqueue_style(
            'report-card-style',
            get_stylesheet_directory_uri() . '/assets/report-card-style.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/report-card-style.css'),
            'all' // This ensures styles apply to both screen and print
        );
    }

    // Ensure report-card-style.css is applied for both screen and print
    if (is_page('reportcard')) {
        wp_enqueue_style(
            'report-card-form',
            get_stylesheet_directory_uri() . '/assets/report-card-form.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/report-card-form.css'),
            'all' // This ensures styles apply to both screen and print
        );
    }

    // Ensure report-card-style.css is applied for both screen and print
    if (is_page('contact-us')) {
        wp_enqueue_style(
            'contactus',
            get_stylesheet_directory_uri() . '/assets/contactus.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/contactus.css'),
            'all' // This ensures styles apply to both screen and print
        );
    }

    // Ensure report-card-style.css is applied for both screen and print
    if (is_page('how-it-works')) {
        wp_enqueue_style(
            'howitworks',
            get_stylesheet_directory_uri() . '/assets/howitworks.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/howitworks.css'),
            'all' // This ensures styles apply to both screen and print
        );
    }
    // Add this function to enqueue necessary scripts
    function enqueue_registration_scripts()
    {
        if (is_page('register')) {  // Adjust this condition based on your page
            wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
            wp_enqueue_script('stripe', 'https://js.stripe.com/v3/', array(), null, true);
        }
    }
    add_action('wp_enqueue_scripts', 'enqueue_registration_scripts');

    function enqueue_ajax_scripts()
    {
        wp_enqueue_script('custom-ajax', get_template_directory_uri() . '/js/custom.js', array('jquery'), null, true);
        wp_localize_script('custom-ajax', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('parent_registration_nonce'),
        ));
    }
    add_action('wp_enqueue_scripts', 'enqueue_ajax_scripts');

    // Enqueue custom scripts
    wp_enqueue_script('custom-scripts', get_stylesheet_directory_uri() . '/script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'twentytwentyfive_child_enqueue_styles');


// Enqueue additional external scripts and styles
function twentytwentyfive_child_enqueue_scripts()
{
    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css');

    // Enqueue Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

    // Enqueue custom script (if you have one)
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/script.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'twentytwentyfive_child_enqueue_scripts');

// Create Database Tables
function santa_report_create_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}parents (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    referral_code VARCHAR(50),
    membership_type ENUM('basic', 'premium') DEFAULT 'basic',
    stripe_customer_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    reset_key VARCHAR(255) DEFAULT NULL
) $charset_collate;

CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kids (
    kid_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES {$wpdb->prefix}parents(user_id) ON DELETE CASCADE
) $charset_collate;

CREATE TABLE IF NOT EXISTS {$wpdb->prefix}grading_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kid_id INT NOT NULL,
    criteria_1 VARCHAR(100) NOT NULL,
    criteria_2 VARCHAR(100) NOT NULL,
    criteria_3 VARCHAR(100) NOT NULL,
    criteria_4 VARCHAR(100) NOT NULL,
    criteria_5 VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kid_id) REFERENCES {$wpdb->prefix}kids(kid_id) ON DELETE CASCADE
) $charset_collate;

CREATE TABLE IF NOT EXISTS {$wpdb->prefix}report_cards (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    kid_id INT NOT NULL,
    report_date DATE NOT NULL, -- Changed from month_year to report_date
    criteria_id INT NOT NULL,
    grade_1 ENUM('red', 'yellow', 'green') NOT NULL,
    grade_2 ENUM('red', 'yellow', 'green') NOT NULL,
    grade_3 ENUM('red', 'yellow', 'green') NOT NULL,
    grade_4 ENUM('red', 'yellow', 'green') NOT NULL,
    grade_5 ENUM('red', 'yellow', 'green') NOT NULL,
    comment_1 VARCHAR(255),  -- New column for each grading criterion comment
    comment_2 VARCHAR(255),  
    comment_3 VARCHAR(255),  
    comment_4 VARCHAR(255),  
    comment_5 VARCHAR(255),  
    santa_comments TEXT,  -- New column for Santa's overall comments
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kid_id) REFERENCES {$wpdb->prefix}kids(kid_id) ON DELETE CASCADE,
    FOREIGN KEY (criteria_id) REFERENCES {$wpdb->prefix}grading_criteria(id) ON DELETE CASCADE
) $charset_collate;

 
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}memberships (
    membership_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    membership_type ENUM('basic', 'premium') DEFAULT 'basic',
    stripe_subscription_id VARCHAR(100),
    status ENUM('active', 'inactive', 'canceled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}parents(user_id) ON DELETE CASCADE
) $charset_collate;

    ";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Run on theme activation
add_action('after_switch_theme', 'santa_report_create_tables');


// Handle parent login
function handle_parent_login()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);

        global $wpdb;
        $parent = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}parents WHERE email = %s",
            $email
        ));

        if ($parent && wp_check_password($password, $parent->password_hash)) {
            // Store the parent's email in the session
            $_SESSION['parent_email'] = $parent->email;
            wp_redirect(home_url('/dashboard'));
            exit;
        } else {
            wp_redirect(add_query_arg('login_error', '1', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'handle_parent_login');

//Link for Membership Area
function membership_area_link()
{
    if (!session_id()) {
        session_start();
    }

    if (isset($_SESSION['parent_email'])) {
        return '<a href="' . home_url('/dashboard') . '" style="background-color: white; color: #dc2626; padding: 4px 8px; border-radius: 3px; text-decoration: none; font-weight: 600; font-size: 0.85rem; display: inline-block; border: 1px solid #dc2626; margin-bottom:6px;">Membership Area</a>';
    }

    return ''; // Return nothing if the user is not logged in
}
add_shortcode('membership_link', 'membership_area_link');

// Handle parent logout
function handle_parent_logout()
{
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        session_unset();
        session_destroy();
        wp_redirect(home_url('/login')); // Redirect to the login page
        exit;
    }
}
add_action('init', 'handle_parent_logout');



// Handle Parent Registration////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




function verify_recaptcha($recaptcha_response)
{
    $secret_key = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';

    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret'   => $secret_key,
            'response' => $recaptcha_response
        ]
    ]);

    if (is_wp_error($response)) {
        error_log('reCAPTCHA verification failed: ' . $response->get_error_message());
        return false;
    }

    $response_data = json_decode(wp_remote_retrieve_body($response), true);

    if (!$response_data || !isset($response_data['success'])) {
        error_log('Invalid reCAPTCHA response structure');
        return false;
    }

    if (!$response_data['success']) {
        error_log('reCAPTCHA failed. Errors: ' . implode(', ', $response_data['error-codes'] ?? []));
    }

    return $response_data['success'];
}


// Handle parent registration with Stripe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    // Sanitize inputs from the registration form
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
    $full_name = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : '';
    $referral_code = isset($_POST['referral_code']) ? sanitize_text_field($_POST['referral_code']) : '';
    $membership_type = isset($_POST['membership_type']) ? sanitize_text_field($_POST['membership_type']) : '';
    $payment_method_id = isset($_POST['payment_method_id']) ? sanitize_text_field($_POST['payment_method_id']) : '';

    // Check if necessary fields are filled
    if (!empty($email) && !empty($password) && !empty($full_name) && !empty($payment_method_id)) {
        global $wpdb;



        // Insert parent data into the database
        $inserted = $wpdb->insert(
            "{$wpdb->prefix}parents", // Table name
            [
                'email' => $email,
                'password_hash' => wp_hash_password($password),
                'full_name' => $full_name,
                'referral_code' => $referral_code,
                'membership_type' => $membership_type,
                'created_at' => current_time('mysql') // Store the registration date/time
            ]
        );

        // If insertion was successful, proceed to create the Stripe customer and subscription
        if ($inserted) {
            $parent_id = $wpdb->insert_id; // Get the inserted parent ID

            // Create Stripe customer and subscription
            create_stripe_customer_and_subscription($parent_id, $email, $full_name, $membership_type, $payment_method_id, $referral_code);

            // First check if this is a POST request with data
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
                wp_die('Invalid request method');
            }

            // Verify nonce if it exists (for form submissions through admin-post.php)
            if (isset($_POST['registration_nonce'])) {
                if (!wp_verify_nonce($_POST['registration_nonce'], 'parent_registration_nonce')) {
                    wp_die('Security check failed');
                }
            }

            // Verify reCAPTCHA if it exists
            if (isset($_POST['g-recaptcha-response'])) {
                if (!verify_recaptcha($_POST['g-recaptcha-response'])) {
                    wp_die('reCAPTCHA verification failed');
                }
            }

            // Redirect to the Thank You page
            wp_redirect(site_url('/thank-you'));
            exit;
        }
    }
}



/**
 * Function to create a Stripe customer and subscription with a referral code and send a thank you email
 */
function create_stripe_customer_and_subscription($parent_id, $parent_email, $full_name, $membership_type, $payment_method_id, $referral_code)
{
    require_once 'includes/stripe-functions.php';

    \Stripe\Stripe::setApiKey('sk_test_51QqQq02Melw1opnFbHe4SAAbuvv8FCtySqEZGJBLZYVil8XpMFRnEK3cQA2IbnT30nqCLqP1K9iApRNl5YLd4CU400Dj8MKHhd');

    // Create Stripe customer
    $customer_data = [
        'email' => $parent_email,
        'name' => $full_name,
        'metadata' => []
    ];

    if ($referral_code) {
        $customer_data['metadata']['referral_code'] = $referral_code;
    }

    $customer = \Stripe\Customer::create($customer_data);

    // Attach the payment method to the customer
    $payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
    $payment_method->attach(['customer' => $customer->id]);

    \Stripe\Customer::update($customer->id, [
        'invoice_settings' => [
            'default_payment_method' => $payment_method_id,
        ],
    ]);

    // Select price ID based on membership type
    $price_id = ($membership_type === 'premium') ? 'price_1QrzvQ2Melw1opnF2nvqQ2gM' : 'price_1QrzvQ2Melw1opnF2nvqQ2gM';



    /////////Stripe Registration Coupon Code///////////
    /////////Stripe Registration Coupon Code///////////
    /////////Stripe Registration Coupon Code///////////
    if (!empty($referral_code)) {
        try {
            error_log("Trying to retrieve promotion code: " . $referral_code);

            // Retrieve the promotion code by its code (not ID)
            $promo = \Stripe\PromotionCode::all([
                'code' => $referral_code,
                'limit' => 1
            ]);

            if (!empty($promo->data)) {
                $promo = $promo->data[0]; // Get the first matching promotion code
                error_log("Retrieved promotion code object: " . print_r($promo, true));

                if ($promo && $promo->active && $promo->coupon) {
                    error_log("Applying coupon: " . $promo->coupon->id);
                    $coupon_id = $promo->coupon->id;
                    $coupon_metadata = $promo->coupon->metadata;  // Store coupon metadata
                } else {
                    error_log("Promotion code not found, not active, or no associated coupon: " . $referral_code);
                }
            } else {
                error_log("No promotion code found for: " . $referral_code);
            }
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            error_log('Stripe Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Error: ' . $e->getMessage());
        }
    } else {
        error_log("No referral code provided, skipping coupon logic.");
    }

    // Only fetch available coupons if no coupon_id was set from the referral code
    if (!$coupon_id && !empty($referral_code)) {
        try {
            error_log("Fetching available coupons...");
            $coupons = \Stripe\Coupon::all(['limit' => 10]); // Limit the number of coupons fetched
            if (count($coupons->data) > 0) {
                // Use the first available coupon
                $coupon_id = $coupons->data[0]->id;
                $coupon_metadata = $coupons->data[0]->metadata;  // Store metadata of the first coupon
                error_log("Using available coupon: " . $coupon_id);
            }
        } catch (Exception $e) {
            error_log('Stripe Coupon Fetch Error: ' . $e->getMessage());
        }
    }

    // Now you can use the coupon_id and coupon_metadata
    // For example, you can include coupon metadata in the subscription data
    $subscription_data = [
        'customer' => $customer->id,
        'items' => [['price' => $price_id]], // Attach price
        'metadata' => [
            'coupon_metadata' => json_encode($coupon_metadata),  // Store coupon metadata in subscription metadata
        ],
    ];

    // Apply the coupon if available
    if ($coupon_id) {
        $subscription_data['coupon'] = $coupon_id;
    }

    // Create the Stripe subscription
    try {
        $subscription = \Stripe\Subscription::create($subscription_data);

        // Store Stripe customer ID in the database
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}parents",
            ['stripe_customer_id' => $customer->id],
            ['user_id' => $parent_id]
        );

        // Insert subscription data into memberships table
        $wpdb->insert(
            "{$wpdb->prefix}memberships",
            [
                'user_id' => $parent_id,
                'membership_type' => $membership_type,
                'stripe_subscription_id' => $subscription->id,
                'status' => 'active',
                'created_at' => current_time('mysql'),
                'referral_code' => $referral_code
            ]
        );

        // Send thank you email
        $subject = 'Thank You for Registering!';
        $message = "Hi {$full_name},\n\n";
        $message .= "Thank you for registering and subscribing to our service. We are excited to have you on board!\n\n";
        if ($referral_code) {
            $message .= "You used the referral code: {$referral_code}. Your discount has been applied.\n\n";
        }
        $message .= "If you have any questions, feel free to contact us.\n\n";
        $message .= "Thanks,\n";
        $message .= "Santa Report Card Team";

        $headers = ['Content-Type: text/html; charset=UTF-8', 'From: Santa Report Card <no-reply@santareportcard.com>'];
        wp_mail($parent_email, $subject, $message, $headers);

        // Redirect to Thank You page
        wp_redirect(site_url('/thank-you'));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Handle Stripe subscription creation error
        error_log('Stripe Subscription Creation Error: ' . $e->getMessage());
        wp_die('There was an issue creating your subscription. Please try again later.');
    }
}



// Register REST API endpoint to fetch Stripe prices
add_action('rest_api_init', function () {
    register_rest_route('santa-report-card/v1', '/prices', array(
        'methods' => 'GET',
        'callback' => 'get_stripe_prices',
    ));
});

function get_stripe_prices()
{
    require_once 'includes/stripe-functions.php';

    \Stripe\Stripe::setApiKey('sk_test_51QqQq02Melw1opnFbHe4SAAbuvv8FCtySqEZGJBLZYVil8XpMFRnEK3cQA2IbnT30nqCLqP1K9iApRNl5YLd4CU400Dj8MKHhd'); // Replace with your secret key

    try {
        $prices = \Stripe\Price::all(['limit' => 10]);
        return $prices->data;
    } catch (Exception $e) {
        return new WP_Error('stripe_error', $e->getMessage(), array('status' => 500));
    }
}

// Register REST API endpoint to fetch Stripe promotion codes
add_action('rest_api_init', function () {
    register_rest_route('santa-report-card/v1', '/promotion-codes', array(
        'methods' => 'GET',
        'callback' => 'get_stripe_promotion_codes',
    ));
});

function get_stripe_promotion_codes()
{
    require_once 'includes/stripe-functions.php';

    \Stripe\Stripe::setApiKey('sk_test_51QqQq02Melw1opnFbHe4SAAbuvv8FCtySqEZGJBLZYVil8XpMFRnEK3cQA2IbnT30nqCLqP1K9iApRNl5YLd4CU400Dj8MKHhd'); // Replace with your secret key

    try {
        $promotion_codes = \Stripe\PromotionCode::all(['limit' => 100]);
        return $promotion_codes->data;
    } catch (Exception $e) {
        return new WP_Error('stripe_error', $e->getMessage(), array('status' => 500));
    }
}


// Handle child registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    global $wpdb;

    $full_name = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : '';
    $parent_email = isset($_SESSION['parent_email']) ? $_SESSION['parent_email'] : '';


    // Fetch parent ID from the database
    $parent = $wpdb->get_row($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->prefix}parents WHERE email = %s",
        $parent_email
    ));

    if ($parent && !empty($full_name)) {
        // Insert child into kids table
        $wpdb->insert(
            "{$wpdb->prefix}kids",
            [
                'parent_id' => $parent->user_id,
                'full_name' => $full_name,
                'created_at' => current_time('mysql')
            ]
        );
        $kid_id = $wpdb->insert_id;

        // Insert grading criteria
        $wpdb->insert(
            "{$wpdb->prefix}grading_criteria",
            [
                'kid_id' => $kid_id,
                'criteria_1' => sanitize_text_field($_POST['criteria_1']),
                'criteria_2' => sanitize_text_field($_POST['criteria_2']),
                'criteria_3' => sanitize_text_field($_POST['criteria_3']),
                'criteria_4' => sanitize_text_field($_POST['criteria_4']),
                'criteria_5' => sanitize_text_field($_POST['criteria_5']),
                'created_at' => current_time('mysql')
            ]
        );

        // Redirect to Thank You page
        wp_redirect(site_url('/dashboard'));
        exit;
    }
}

function src_child_registration_form()
{
    if (!isset($_SESSION['parent_email'])) {
        return '<p>You must be logged in to register a child.</p>';
    }

    $criteria_options = [
        "Helping make the bed",
        "Picking up toys and books",
        "Putting laundry in the hamper or in the laundry room",
        "Helping to feed pets",
        "Helping to wipe up messes",
        "Dusting with socks on their hands",
        "Putting small items in a dishwasher",
        "Dry mopping in small areas with help to maneuver the mop",
        "Helping to clear and set the table",
        "Making bed independently",
        "Dusting",
        "Helping out to cook and prepare food",
        "Help carry groceries in the house",
        "Sorting laundry whites and colors",
        "Watering plants using a small container",
        "Pulling garden weeds",
        "Washing small dishes at the sink",
        "Helping to clean their room",
        "Putting away groceries",
        "Taking care of pets",
        "Vacuuming, sweeping, mopping, wiping down surfaces",
        "Empty indoor trash cans and taking it outside",
        "Folding and putting away laundry",
        "Making their snacks, breakfast, and bagged lunches",
        "Emptying and loading the dishwasher",
        "Walking the dog with pooper-scooper supervision",
        "Raking leaves",
        "Clean their bedroom",
        "Assist with making dinner",
        "Being respectful",
        "Good listener",
        "Sharing",
        "Eating healthy",
        "Being helpful",
        "Good manners",
        "Being a good friend",
        "Donating or volunteering",
        "Going to church",
        "Positive attitude",
        "Honesty (Telling the truth)",
        "Confident",
        "Kindness",
        "Forgiving",
        "Taking risks",
        "Compassion/Empathetic",
        "Good grades",
        "Hard worker (work ethic)",
        "Supportive",
        "Good friend"
    ];

    ob_start(); ?>
    <style>
        .child-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .child-form h4 {
            text-align: center;
            margin-bottom: 20px;
        }

        .child-form .wp-block-columns {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .child-form .wp-block-column {
            flex: 1;
            min-width: 200px;
            padding: 10px;
        }

        .child-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .child-form input,
        .child-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .child-form button {
            width: 100%;
            padding: 10px;
            background: rgb(229, 15, 4);
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .child-form button:hover {
            background: rgb(224, 10, 10);
        }
    </style>
    <form id="child-registration-form" method="POST" class="wp-block-group child-form">
        <div class="wp-block-columns">
            <div class="wp-block-column">
                <label for="full_name">Child's Full Name</label>
                <input type="text" name="full_name" id="full_name" required placeholder="Enter child's name">
            </div>
        </div>

        <h4>Grading Criteria</h4>

        <?php for ($i = 1; $i <= 5; $i++) : ?>
            <div class="wp-block-columns">
                <div class="wp-block-column">
                    <label for="criteria_<?php echo $i; ?>">Criteria <?php echo $i; ?></label>
                    <select name="criteria_<?php echo $i; ?>" id="criteria_<?php echo $i; ?>" required>
                        <option value="">Select criteria</option>
                        <?php foreach ($criteria_options as $option) : ?>
                            <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endfor; ?>

        <div class="wp-block-columns">
            <div class="wp-block-column">
                <button type="submit" class="wp-block-button__link">Register Child</button>
            </div>
        </div>
    </form>

<?php return ob_get_clean();
}
add_shortcode('child_registration_form', 'src_child_registration_form');

// Report Card Form
function get_child_grading_criteria($kid_id)
{
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}grading_criteria WHERE kid_id = %d", $kid_id));
}

function handle_report_card_submission()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kid_id'])) {
        global $wpdb;

        $kid_id = intval($_POST['kid_id']);
        $comments = sanitize_textarea_field($_POST['santa_comments']);

        // Fetch the grading criteria for the kid
        $criteria = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}grading_criteria WHERE kid_id = %d",
            $kid_id
        ));

        if (!$criteria) {
            error_log("No grading criteria found for kid_id: {$kid_id}");
            wp_redirect(home_url('/dashboard?error=no_criteria'));
            exit;
        }

        // Get the current date in 'YYYY-MM-DD' format
        $report_date = date('Y-m-d'); // FIX: Define report_date
        error_log("Report Date: {$report_date}"); // Debug log

        // Insert a single row with all grades and comments
        $wpdb->insert("{$wpdb->prefix}report_cards", [
            'kid_id' => $kid_id,
            "report_date" => $report_date, // FIX: Now defined properly
            'criteria_id' => $criteria->id,
            'grade_1' => sanitize_text_field($_POST['criteria_1']),
            'grade_2' => sanitize_text_field($_POST['criteria_2']),
            'grade_3' => sanitize_text_field($_POST['criteria_3']),
            'grade_4' => sanitize_text_field($_POST['criteria_4']),
            'grade_5' => sanitize_text_field($_POST['criteria_5']),
            'comment_1' => sanitize_text_field($_POST['comment_1']),
            'comment_2' => sanitize_text_field($_POST['comment_2']),
            'comment_3' => sanitize_text_field($_POST['comment_3']),
            'comment_4' => sanitize_text_field($_POST['comment_4']),
            'comment_5' => sanitize_text_field($_POST['comment_5']),
            'santa_comments' => $comments,
            'created_at' => current_time('mysql')
        ]);

        wp_redirect(home_url('/dashboard?success=report_card_created'));
        exit;
    }
}
add_action('init', 'handle_report_card_submission');


// Generate Report Card Form Shortcode
function generate_report_card_form()
{
    if (!isset($_SESSION['parent_email'])) {
        return '<p>You must be logged in to create a report card.</p>';
    }

    global $wpdb;
    $parent_email = $_SESSION['parent_email'];
    $parent = $wpdb->get_row($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}parents WHERE email = %s", $parent_email));
    if (!$parent) return '<p>Parent not found.</p>';

    $kids = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}kids WHERE parent_id = %d", $parent->user_id));
    if (!$kids) return '<p>No children registered.</p>';

    ob_start(); ?>
    <form method="POST">
        <label for="kid_id">Select Child</label>
        <select name="kid_id" id="kid_id" required onchange="fetchCriteria()">
            <option value="">-- Select Child --</option>
            <?php foreach ($kids as $kid) : ?>
                <option value="<?php echo $kid->kid_id; ?>"><?php echo esc_html($kid->full_name); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="report_date">Report Date</label>
        <input type="date" name="report_date" id="report_date" required value="<?php echo date('Y-m-d'); ?>">

        <h4>Grading Criteria</h4>
        <div id="grading-criteria"></div>

        <label for="santa_comments">Santa's Comments</label>
        <textarea name="santa_comments" id="santa_comments" placeholder="Enter Santa's overall comments" required></textarea>

        <button type="submit">Submit Report Card</button>
    </form>

    <script>
        function fetchCriteria() {
            var kid_id = document.getElementById('kid_id').value;
            if (kid_id) {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=get_grading_criteria&kid_id=' + kid_id)
                    .then(response => response.json())
                    .then(data => {
                        let criteriaDiv = document.getElementById('grading-criteria');
                        criteriaDiv.innerHTML = '';
                        for (let i = 1; i <= 5; i++) {
                            criteriaDiv.innerHTML +=
                                `<label>${data['criteria_' + i]}</label>
                                <select name="criteria_${i}" required>
                                    <option value="green" style="background-color: #008000; color: white;">Green</option>
                                    <option value="yellow" style="background-color: #FFD700; color: black;">Yellow</option>
                                    <option value="red" style="background-color: #FF0000; color: white;">Red</option>
                                </select>
                                <textarea name="comment_${i}" placeholder="Enter comments for this criterion"></textarea>`;
                        }
                    })
                    .catch(error => console.error("Error fetching grading criteria:", error));
            }
        }
    </script>
<?php return ob_get_clean();
}
add_shortcode('generate_report_card_form', 'generate_report_card_form');

// Get Grading Criteria via AJAX
function get_grading_criteria_ajax()
{
    global $wpdb;
    $kid_id = intval($_GET['kid_id']);
    $criteria = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}grading_criteria WHERE kid_id = %d", $kid_id));
    echo json_encode($criteria);
    wp_die();
}
add_action('wp_ajax_get_grading_criteria', 'get_grading_criteria_ajax');
add_action('wp_ajax_nopriv_get_grading_criteria', 'get_grading_criteria_ajax');

// Display Report Cards on Dashboard with View & Print Button
// Display Report Cards on Dashboard with View, Print, and Delete Button
function display_report_cards()
{
    if (!isset($_SESSION['parent_email'])) {
        return '<p>Please log in to view report cards.</p>';
    }

    global $wpdb;
    $parent_email = $_SESSION['parent_email'];

    // Fetch parent ID
    $parent = $wpdb->get_row($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}parents WHERE email = %s", $parent_email));
    if (!$parent) return '<p>Parent not found.</p>';

    // Fetch children
    $kids = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}kids WHERE parent_id = %d", $parent->user_id));

    if (!$kids) return '<p>No children registered.</p>';

    ob_start();
    echo '<h3>Your Children’s Report Cards</h3>';

    foreach ($kids as $kid) {
        echo "<h4>{$kid->full_name}</h4>";
        $report_cards = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}report_cards WHERE kid_id = %d ORDER BY created_at DESC", $kid->kid_id));

        if (!$report_cards) {
            echo "<p>No report cards available.</p>";
            continue;
        }

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Report Date</th><th>View & Print</th><th>Delete</th></tr></thead><tbody>';

        foreach ($report_cards as $report) {
            $report_date = !empty($report->report_date) ? esc_html($report->report_date) : 'N/A';

            echo "<tr>
                <td>{$report_date}</td>
                <td><a href='" . esc_url(get_permalink(get_page_by_path('report-card-view')) . "?report_id={$report->report_id}") . "' target='_blank' class='button'>View & Print</a></td>
                <td>
                    <form method='POST' onsubmit='return confirm(\"Are you sure you want to delete this report card?\");'>
                        <input type='hidden' name='delete_report_id' value='{$report->report_id}'>
                        <button type='submit' class='button button-danger'>Delete</button>
                    </form>
                </td>
            </tr>";
        }

        echo '</tbody></table>';
    }

    return ob_get_clean();
}
add_shortcode('display_report_cards', 'display_report_cards');

// Handle report card deletion
function handle_report_card_deletion()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_report_id'])) {
        global $wpdb;
        $report_id = intval($_POST['delete_report_id']);

        // Delete the report card
        $wpdb->delete("{$wpdb->prefix}report_cards", ['report_id' => $report_id]);

        // Redirect to the dashboard to avoid resubmission
        wp_redirect(home_url('/dashboard'));
        exit;
    }
}
add_action('init', 'handle_report_card_deletion');


// Display Report Card Data

function inject_report_card_data()
{
    if (!is_page('report-card-view') || !isset($_GET['report_id'])) {
        return;
    }

    global $wpdb;
    $report_id = intval($_GET['report_id']);

    // Fetch the current report card
    $current_report = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}report_cards WHERE report_id = %d", $report_id));

    if (!$current_report) {
        echo '<script>document.querySelector(".wp-block-group").innerHTML = "<p>Report card not found.</p>";</script>';
        return;
    }


    // Try to fetch the previous month's report first
    $previous_month_date = date('Y-m-d', strtotime('-0 month', strtotime($current_report->report_date)));
    $previous_report = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}report_cards WHERE kid_id = %d AND report_date = %s", $current_report->kid_id, $previous_month_date));

    // If no report is found for the same month, check for one month prior
    if (!$previous_report) {
        $previous_month_date = date('Y-m-d', strtotime('-1 month', strtotime($current_report->report_date)));
        $previous_report = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}report_cards WHERE kid_id = %d AND report_date = %s", $current_report->kid_id, $previous_month_date));
    }

    // Fetch the child and grading criteria data
    $kid = $wpdb->get_row($wpdb->prepare("SELECT full_name FROM {$wpdb->prefix}kids WHERE kid_id = %d", $current_report->kid_id));
    $criteria = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}grading_criteria WHERE id = %d", $current_report->criteria_id));

    if (!$criteria) {
        echo '<script>document.querySelector(".wp-block-group").innerHTML = "<p>No grading criteria found.</p>";</script>';
        return;
    }

    // Define grade mappings
    $grade_values = ["green" => 3, "yellow" => 2, "red" => 1];
    $grade_colors = ["green" => "#008000", "yellow" => "#FFD700", "red" => "#FF0000", "N/A" => "#A9A9A9"];

    // Generate the table HTML
    $report_table_html = build_report_table($current_report, $previous_report, $criteria, $grade_values, $grade_colors);

    // Inject data into the page
    echo "<script>
        document.querySelector('.child-name').textContent = '" . esc_js($kid->full_name) . "';
        document.querySelector('.report-date').textContent = '" . esc_js($current_report->report_date) . "';
        document.querySelector('.report-comments').innerHTML = '" . nl2br(esc_js($current_report->santa_comments)) . "';
        document.querySelector('.report-card-table').innerHTML = `{$report_table_html}`;
    </script>";
}
add_action('wp_footer', 'inject_report_card_data');




function build_report_table($current_report, $previous_report, $criteria, $grade_values, $grade_colors)
{
    $table_html = "<table style='width: 100%; border-collapse: collapse; text-align: center;'>
            <thead>
                <tr style='background-color: #f4f4f4;'>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Criteria</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Current</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Previous</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Overall</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Comments</th>
                </tr>
            </thead>
            <tbody>";

    $current_total = $previous_total = $overall_total = 0;
    $current_count = $previous_count = $overall_count = 0;

    for ($i = 1; $i <= 5; $i++) {
        $criteria_name = isset($criteria->{"criteria_$i"}) ? esc_html($criteria->{"criteria_$i"}) : "Unnamed Criteria";

        // Get current and previous grades
        $current_grade = strtolower(trim(esc_html($current_report->{"grade_$i"})));
        $previous_grade = $previous_report ? strtolower(trim(esc_html($previous_report->{"grade_$i"}))) : "N/A";

        // Convert grades to numeric values
        $current_value = $grade_values[$current_grade] ?? null;
        $previous_value = isset($grade_values[$previous_grade]) ? $grade_values[$previous_grade] : null;

        // Calculate average if both are available
        if ($current_value !== null && $previous_value !== null) {
            $average_value = round(($current_value + $previous_value) / 2);
        } elseif ($current_value !== null) {
            $average_value = $current_value;
        } elseif ($previous_value !== null) {
            $average_value = $previous_value;
        } else {
            $average_value = null;
        }

        // Convert numeric average back to a grade
        $average_grade = array_search($average_value, $grade_values) ?? "N/A";

        // Get colors for each grade
        $current_color = $grade_colors[$current_grade] ?? "#A9A9A9";
        $previous_color = $grade_colors[$previous_grade] ?? "#A9A9A9";
        $average_color = $grade_colors[$average_grade] ?? "#A9A9A9";

        // Accumulate totals for averages
        if ($current_value !== null) {
            $current_total += $current_value;
            $current_count++;
        }
        if ($previous_value !== null) {
            $previous_total += $previous_value;
            $previous_count++;
        }
        if ($average_value !== null) {
            $overall_total += $average_value;
            $overall_count++;
        }

        // Get comments for the current criterion
        $current_comment = esc_html($current_report->{"comment_$i"});

        $table_html .= "<tr>
                <td style='padding: 10px; border: 1px solid #ddd;'>$criteria_name</td>
                <td style='padding: 10px; border: 1px solid #ddd; background-color: $current_color; color: white; font-weight: bold;'>" . strtoupper($current_grade) . "</td>
                <td style='padding: 10px; border: 1px solid #ddd; background-color: $previous_color; color: white; font-weight: bold;'>" . strtoupper($previous_grade) . "</td>
                <td style='padding: 10px; border: 1px solid #ddd; background-color: $average_color; color: white; font-weight: bold;'>" . strtoupper($average_grade) . "</td>
                <td class='current-comment' style='padding: 10px; border: 1px solid #ddd;'>$current_comment</td>
            </tr>";
    }

    // Calculate overall averages
    $final_current_value = $current_count > 0 ? round($current_total / $current_count) : null;
    $final_previous_value = $previous_count > 0 ? round($previous_total / $previous_count) : null;
    $final_overall_value = $overall_count > 0 ? round($overall_total / $overall_count) : null;

    // Convert back to grades
    $final_current_grade = array_search($final_current_value, $grade_values) ?? "N/A";
    $final_previous_grade = array_search($final_previous_value, $grade_values) ?? "N/A";
    $final_overall_grade = array_search($final_overall_value, $grade_values) ?? "N/A";

    // Get colors for final averages
    $final_current_color = $grade_colors[$final_current_grade] ?? "#A9A9A9";
    $final_previous_color = $grade_colors[$final_previous_grade] ?? "#A9A9A9";
    $final_overall_color = $grade_colors[$final_overall_grade] ?? "#A9A9A9";

    // Append the totals row
    $table_html .= "<tr style='font-weight: bold; background-color: #f4f4f4;'>
            <td style='padding: 10px; border: 1px solid #ddd;'>Average</td>
            <td style='padding: 10px; border: 1px solid #ddd; background-color: $final_current_color; color: white;'>" . strtoupper($final_current_grade) . "</td>
            <td style='padding: 10px; border: 1px solid #ddd; background-color: $final_previous_color; color: white;'>" . strtoupper($final_previous_grade) . "</td>
            <td style='padding: 10px; border: 1px solid #ddd; background-color: $final_overall_color; color: white;'>" . strtoupper($final_overall_grade) . "</td>
            <td style='padding: 10px; border: 1px solid #ddd;'></td>
        </tr>";

    $table_html .= "</tbody></table>";
    return $table_html;
    // Generate the table HTML
    $report_table_html = build_report_table($current_report, $previous_report, $criteria, $grade_values, $grade_colors);

    // Inject data into the page
    echo "<script>
        document.querySelector('.child-name').textContent = '" . esc_js($kid->full_name) . "';
        document.querySelector('.report-date').textContent = '" . esc_js($current_report->report_date) . "';
        document.querySelector('.report-comments').innerHTML = '" . nl2br(esc_js($current_report->comments)) . "';
        document.querySelector('.report-card-table').innerHTML = `${report_table_html}`;
    </script>";
}
add_action('wp_footer', 'inject_report_card_data');






// Dynamically Update Dashboard
function get_dashboard_overview()
{
    // Check if the parent is logged in
    if (!isset($_SESSION['parent_email'])) {
        return '<p>You must be logged in to access the dashboard.</p>';
    }

    global $wpdb;
    $parent_email = $_SESSION['parent_email'];

    // Fetch the parent's data from the wp_parents table
    $parent = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}parents WHERE email = %s",
        $parent_email
    ));

    if (!$parent) {
        return '<p>No parent data found.</p>';
    }

    // Fetch membership type (use prepared statement to prevent SQL injection)
    $membership = $wpdb->get_var($wpdb->prepare(
        "SELECT membership_type FROM {$wpdb->prefix}memberships WHERE user_id = %d",
        $parent->user_id
    ));

    // Fallback to 'Basic' if no membership is found
    if (empty($membership)) {
        $membership = 'basic';
    }

    // Fetch the number of kids (use prepared statement to prevent SQL injection)
    $kid_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}kids WHERE parent_id = %d",
        $parent->user_id
    ));

    // Build the HTML output
    $output = '<div class="wp-block-columns overview-grid">';
    $output .= '<div class="wp-block-column overview-item blue">';
    $output .= '<p>Membership Status</p>';
    $output .= '<p>' . esc_html(ucfirst($membership)) . '</p>';
    $output .= '</div>';
    $output .= '<div class="wp-block-column overview-item red">';
    $output .= '<p>Parent Email</p>';
    $output .= '<p>' . esc_html($parent->email) . '</p>';
    $output .= '</div>';
    $output .= '<div class="wp-block-column overview-item green">';
    $output .= '<p>Number of Kids</p>';
    $output .= '<p>' . esc_html($kid_count) . '</p>';
    $output .= '</div>';
    $output .= '<div class="wp-block-column">';
    $output .= '<div class="wp-block-button">';
    $output .= '<a class="wp-block-button__link" href="/reportcard">Generate Report Card</a>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}
add_shortcode('dashboard_overview', 'get_dashboard_overview');


//Dashboard Navigation
function src_dashboard_navigation()
{
    $logout_url = home_url('/dashboard?action=logout'); // Adjust the logout URL to fit your setup

    ob_start(); ?>

    <nav class="dashboard-navigation">
        <ul>
            <li><a href="/">🏠 Home</a></li>
            <li><a href="/childregister">👥 Register Child</a></li>
            <li><a href="<?php echo esc_url($logout_url); ?>">🚪 Logout</a></li>
        </ul>
    </nav>

<?php return ob_get_clean();
}
add_shortcode('dashboard_nav', 'src_dashboard_navigation');



// Dynamically fetch registered parent
function src_get_registered_parents()
{
    global $wpdb;
    $table_name = "{$wpdb->prefix}parents";

    // Check if the parent is logged in
    if (!isset($_SESSION['parent_email'])) {
        return '<p>You must be logged in to view this data.</p>';
    }

    $parent_email = $_SESSION['parent_email'];

    // Fetch data for the current parent only, based on their email
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE email = %s ORDER BY created_at DESC",
        $parent_email
    ), ARRAY_A);

    if (!$results) {
        return '<p>No registered parents yet.</p>';
    }

    $output = '<table class="wp-list-table widefat fixed striped">';
    $output .= '<thead><tr><th>Email</th><th>Full Name</th><th>Membership</th><th>Registered On</th></tr></thead><tbody>';

    foreach ($results as $row) {
        $output .= "<tr>
            <td>{$row['email']}</td>
            <td>{$row['full_name']}</td>
            <td>{$row['membership_type']}</td>
            <td>{$row['created_at']}</td>
        </tr>";
    }

    $output .= '</tbody></table>';

    return $output;
}


// Register as a shortcode to display in Gutenberg or a page
add_shortcode('src_registered_parents', 'src_get_registered_parents');



// Shortcode to display the forgot password form
// Shortcode to display the forgot password form
// Shortcode to display the forgot password form
function forgot_password_form()
{
    ob_start();
?>
    <div class="forgot-password-container">
        <form class="forgot-password-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="forgot_password">
            <label for="user_login">Enter Your Email To Reset Password:</label>
            <input type="email" name="user_login" id="user_login" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('forgot_password_form', 'forgot_password_form');

// Shortcode to display the password reset form
function password_reset_form()
{
    if (!isset($_GET['key']) || !isset($_GET['login'])) {
        return '<p>Invalid password reset link.</p>';
    }

    ob_start();
?>
    <style>
        .password-reset-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            /* Full viewport height */
            background-color: #f9f9f9;
            /* Light background color */
        }

        .password-reset-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }

        .password-reset-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .password-reset-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .password-reset-form button {
            width: 100%;
            padding: 10px;
            background: #e50f04;
            /* Red background color */
            color: #fff;
            /* White text color */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .password-reset-form button:hover {
            background: #d10a0a;
            /* Darker red on hover */
        }
    </style>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="key" value="<?php echo esc_attr($_GET['key']); ?>">
        <input type="hidden" name="login" value="<?php echo esc_attr($_GET['login']); ?>">
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required>
        <button type="submit">Reset Password</button>
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('password_reset_form', 'password_reset_form');

// Handle the forgot password request
function handle_forgot_password()
{
    if (isset($_POST['user_login'])) {
        global $wpdb;
        $user_login = sanitize_email($_POST['user_login']);
        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}parents WHERE email = %s", $user_login));

        if ($user) {
            // Generate the reset key
            $reset_key = wp_generate_password(20, false);

            // Store the reset key in the database
            $wpdb->update(
                "{$wpdb->prefix}parents",
                array('reset_key' => $reset_key),
                array('user_id' => $user->user_id)
            );

            // Create the reset URL
            $reset_url = add_query_arg(array(
                'action' => 'rp',
                'key' => $reset_key,
                'login' => rawurlencode($user->email)
            ), home_url('/passwordreset'));

            // Send the reset email
            $subject = 'Password Reset Request';
            $message = "Hi {$user->full_name},\n\n";
            $message .= "To reset your password, please click the following link:\n\n";
            $message .= $reset_url . "\n\n";
            $message .= "If you did not request a password reset, please ignore this email.\n\n";
            $message .= "Thanks,\n";
            $message .= "Santa Report Card Team";

            // Set the headers to include the custom "From" name and email address
            $headers = array('Content-Type: text/html; charset=UTF-8', 'From: Santa Report Card <no-reply@santareportcard.com>');

            wp_mail($user->email, $subject, $message, $headers);

            // Redirect to a confirmation page
            wp_redirect(home_url('/passwordresetconfirmation'));
            exit;
        } else {
            // Redirect to an error page
            wp_redirect(home_url('/passwordreseterror'));
            exit;
        }
    }
}
add_action('admin_post_nopriv_forgot_password', 'handle_forgot_password');
add_action('admin_post_forgot_password', 'handle_forgot_password');

// Handle the password reset request
function handle_password_reset()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key']) && isset($_POST['login']) && isset($_POST['new_password'])) {
        global $wpdb;
        $key = sanitize_text_field($_POST['key']);
        $login = sanitize_text_field($_POST['login']);
        $new_password = sanitize_text_field($_POST['new_password']);

        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}parents WHERE email = %s AND reset_key = %s", $login, $key));

        if ($user) {
            // Update the user's password
            $wpdb->update(
                "{$wpdb->prefix}parents",
                array('password_hash' => wp_hash_password($new_password), 'reset_key' => ''),
                array('user_id' => $user->user_id)
            );

            // Redirect to a confirmation page
            wp_redirect(home_url('/passwordresetsuccess'));
            exit;
        } else {
            // Redirect to an error page
            wp_redirect(home_url('/passwordreseterror'));
            exit;
        }
    }
}

// Handle the contact form submission
function handle_contact_form_submission()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_contact_form') {
        $first_name = sanitize_text_field($_POST['first-name']);
        $last_name = sanitize_text_field($_POST['last-name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $contact_methods = isset($_POST['contact-method']) ? array_map('sanitize_text_field', $_POST['contact-method']) : [];
        $contact_time = isset($_POST['contact-time']) ? sanitize_text_field($_POST['contact-time']) : '';
        $help = sanitize_textarea_field($_POST['help']);

        $to = 'info@santareportcard.com';
        $subject = 'New Contact Form Submission';
        $message = "First Name: $first_name\n";
        $message .= "Last Name: $last_name\n";
        $message .= "Email: $email\n";
        $message .= "Phone: $phone\n";
        $message .= "Preferred Contact Methods: " . implode(', ', $contact_methods) . "\n";
        $message .= "Preferred Contact Time: $contact_time\n";
        $message .= "Message: $help\n";

        $headers = array('Content-Type: text/plain; charset=UTF-8', 'From: Santa Report Card <no-reply@santareportcard.com>');

        if (wp_mail($to, $subject, $message, $headers)) {
            // Redirect to a thank you page or display a success message
            wp_redirect(home_url('/thank-you'));
            exit;
        } else {
            // Handle the error
            wp_redirect(home_url('/contact-error'));
            exit;
        }
    }
}


// Add function to get Stripe price
// Update the get_stripe_price function to use the same logic
function get_stripe_price()
{
    try {
        require_once get_stylesheet_directory() . '/includes/stripe-functions.php';
        \Stripe\Stripe::setApiKey('sk_test_51QqQq02Melw1opnFbHe4SAAbuvv8FCtySqEZGJBLZYVil8XpMFRnEK3cQA2IbnT30nqCLqP1K9iApRNl5YLd4CU400Dj8MKHhd');

        // First, get the product ID for 'basic'
        $products = \Stripe\Product::search([
            'query' => 'active:\'true\' AND name:\'basic\'',
        ]);

        if (empty($products->data)) {
            error_log('Stripe Error: No basic product found');
            return '59.99';
        }

        // Get the first active price for this product
        $prices = \Stripe\Price::all([
            'active' => true,
            'product' => $products->data[0]->id,
            'limit' => 1
        ]);

        if (empty($prices->data)) {
            error_log('Stripe Error: No active price found for basic product');
            return '59.99';
        }

        return number_format($prices->data[0]->unit_amount / 100, 2);
    } catch (Exception $e) {
        error_log('Stripe Price Error: ' . $e->getMessage());
        return '59.99';
    }
}

// Update check_referral_discount function
function check_referral_discount()
{
    if (!isset($_POST['referral_code'])) {
        wp_send_json_error(['message' => 'No referral code provided.']);
        return;
    }

    $referral_code = sanitize_text_field($_POST['referral_code']);
    $original_price = floatval(get_stripe_price()); // Get price from Stripe

    try {
        require_once get_stylesheet_directory() . '/includes/stripe-functions.php';
        \Stripe\Stripe::setApiKey('sk_test_51QqQq02Melw1opnFbHe4SAAbuvv8FCtySqEZGJBLZYVil8XpMFRnEK3cQA2IbnT30nqCLqP1K9iApRNl5YLd4CU400Dj8MKHhd');

        // Try to retrieve the promotion code
        $promotions = \Stripe\PromotionCode::all([
            'code' => $referral_code,
            'active' => true,
            'limit' => 1
        ]);

        if (empty($promotions->data)) {
            wp_send_json_error(['message' => 'Invalid referral code']);
            return;
        }

        $promotion = $promotions->data[0];
        $coupon = $promotion->coupon;

        if (!$coupon->valid) {
            wp_send_json_error(['message' => 'Expired referral code']);
            return;
        }

        // Calculate new price
        $discount = ($coupon->percent_off)
            ? ($original_price * ($coupon->percent_off / 100))
            : ($coupon->amount_off / 100);
        $new_price = max(0, $original_price - $discount);

        wp_send_json_success([
            'new_price' => number_format($new_price, 2),
            'discount_type' => $coupon->percent_off ? 'percent' : 'amount',
            'discount_value' => $coupon->percent_off ?? ($coupon->amount_off / 100)
        ]);
    } catch (Exception $e) {
        wp_send_json_error([
            'message' => 'Error processing referral code',
            'error' => $e->getMessage()
        ]);
    }
}

// Add AJAX actions
add_action('wp_ajax_check_referral_discount', 'check_referral_discount');
add_action('wp_ajax_nopriv_check_referral_discount', 'check_referral_discount');

function get_initial_stripe_price()
{
    try {
        $price = get_stripe_price();
        wp_send_json_success([
            'price' => $price
        ]);
    } catch (Exception $e) {
        error_log('Stripe Price Error: ' . $e->getMessage());
        wp_send_json_error([
            'price' => '59.99',
            'error' => $e->getMessage()
        ]);
    }
}
// Register the AJAX endpoints
add_action('wp_ajax_get_stripe_price', 'get_initial_stripe_price');
add_action('wp_ajax_nopriv_get_stripe_price', 'get_initial_stripe_price');





add_action('init', 'handle_contact_form_submission');
add_action('admin_post_nopriv_reset_password', 'handle_password_reset');
add_action('admin_post_reset_password', 'handle_password_reset');
function enqueue_dashboard_scripts()
{
    if (is_page('dashboard')) { // Ensure this script loads only on the dashboard page
        wp_enqueue_script('dashboard-js', get_template_directory_uri() . '/js/dashboard.js', array('jquery'), null, true);
        wp_localize_script('dashboard-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_dashboard_scripts');
