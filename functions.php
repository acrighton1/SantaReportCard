<?php
/**
 * Twenty Twenty-Five Child Theme functions and definitions
 *
 * @package twentytwentyfive-child
 */

// Enqueue parent and child theme styles
function twentytwentyfive_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Enqueue child theme styles
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
    
    // Enqueue login.css on the login page only
    if (is_page('login')) { // Adjust 'login' to your specific login page slug if necessary
        wp_enqueue_style('login-style', get_stylesheet_directory_uri() . '/assets/login.css', array('parent-style'));
    }

    // Enqueue custom scripts
    wp_enqueue_script('custom-scripts', get_stylesheet_directory_uri() . '/script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'twentytwentyfive_child_enqueue_styles');

// Enqueue additional external scripts and styles
function twentytwentyfive_child_enqueue_scripts() {
    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css');

    // Enqueue Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

    // Enqueue custom script (if you have one)
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/script.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'twentytwentyfive_child_enqueue_scripts');

// Handle parent login
function handle_parent_login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);

        global $wpdb;
        $parent = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}parents WHERE email = %s",
            $email
        ));

        if ($parent && wp_check_password($password, $parent->password_hash)) {
            wp_redirect(home_url('/dashboard'));
            exit;
        } else {
            wp_redirect(add_query_arg('login_error', '1', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'handle_parent_login');

//Parent Registration

function handle_parent_registration() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
        global $wpdb;

        // Sanitize input
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        $confirm_password = sanitize_text_field($_POST['confirm_password']);
        $full_name = sanitize_text_field($_POST['full_name']);
        $referral_code = sanitize_text_field($_POST['referral_code']);
        $membership_type = sanitize_text_field($_POST['membership_type']);

        // Password validation
        if ($password !== $confirm_password) {
            wp_redirect(add_query_arg('error', 'password_mismatch', wp_get_referer()));
            exit;
        }

        // Check if email already exists
        $existing_parent = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}parents WHERE email = %s",
            $email
        ));

        if ($existing_parent > 0) {
            wp_redirect(add_query_arg('error', 'email_exists', wp_get_referer()));
            exit;
        }

        // Insert into wp_parents table
        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}parents",
            [
                'full_name' => $full_name,
                'email' => $email,
                'password_hash' => wp_hash_password($password),
                'referral_code' => $referral_code,
                'membership_type' => $membership_type ?? 'basic',
                'stripe_customer_id' => '', // Placeholder for Stripe integration
                'created_at' => current_time('mysql')
            ]
        );
        

        // Redirect to Thank You page
        wp_redirect(home_url('/thank-you'));
        exit;
    }
}

// Hook into WordPress for form processing
add_action('admin_post_nopriv_register_parent', 'handle_parent_registration');
add_action('admin_post_register_parent', 'handle_parent_registration');


function santa_report_create_tables() {
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
    ) $charset_collate;

    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kids (
        kid_id INT AUTO_INCREMENT PRIMARY KEY,
        parent_id INT NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES {$wpdb->prefix}parents(user_id) ON DELETE CASCADE
    ) $charset_collate;

    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}criteria (
        criteria_id INT AUTO_INCREMENT PRIMARY KEY,
        criteria_name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;

    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}report_cards (
        report_id INT AUTO_INCREMENT PRIMARY KEY,
        kid_id INT NOT NULL,
        month_year DATE NOT NULL,
        criteria_id INT NOT NULL,
        grade ENUM('red', 'yellow', 'green') NOT NULL,
        comments TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (kid_id) REFERENCES {$wpdb->prefix}kids(kid_id) ON DELETE CASCADE,
        FOREIGN KEY (criteria_id) REFERENCES {$wpdb->prefix}criteria(criteria_id) ON DELETE CASCADE
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


//Dynamically Update Dashboard

function get_dashboard_overview() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to access the dashboard.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $membership = $wpdb->get_var("SELECT membership_type FROM {$wpdb->prefix}memberships WHERE user_id = $user_id");
    $kid_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}kids WHERE parent_id = $user_id");

    ob_start(); ?>
    <div class="wp-block-columns overview-grid">
        <div class="wp-block-column overview-item blue">
            <p>Membership Status</p>
            <p><?php echo esc_html(ucfirst($membership) ?: 'Basic'); ?></p>
        </div>
        <div class="wp-block-column overview-item green">
            <p>Number of Kids</p>
            <p><?php echo esc_html($kid_count); ?></p>
        </div>
        <div class="wp-block-column">
            <div class="wp-block-button">
                <a class="wp-block-button__link" href="#">Generate Report Card</a>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}
add_filter('the_content', 'do_shortcode');
add_shortcode('dashboard_overview', 'get_dashboard_overview');


//Dynamically fetch Children in Drop down and Associated Report Card

function get_child_dropdown() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to manage your kids.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $kids = $wpdb->get_results("SELECT kid_id, full_name FROM {$wpdb->prefix}kids WHERE parent_id = $user_id");

    if (!$kids) {
        return '<p>No children found. <a href="#">Add a child</a></p>';
    }

    ob_start(); ?>
    <select id="child-select">
        <option value="">Select a child</option>
        <?php foreach ($kids as $kid) : ?>
            <option value="<?php echo esc_attr($kid->kid_id); ?>">
                <?php echo esc_html($kid->full_name); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php return ob_get_clean();
}
add_shortcode('child_dropdown', 'get_child_dropdown');

function get_report_card_callback() {
    if (!is_user_logged_in() || empty($_POST['kid_id'])) {
        wp_send_json_error('Unauthorized or missing child ID.');
    }

    global $wpdb;
    $kid_id = intval($_POST['kid_id']);
    $reports = $wpdb->get_results($wpdb->prepare(
        "SELECT r.grade, r.comments, c.criteria_name 
         FROM {$wpdb->prefix}report_cards r
         JOIN {$wpdb->prefix}criteria c ON r.criteria_id = c.criteria_id
         WHERE r.kid_id = %d", 
         $kid_id
    ));

    if (!$reports) {
        wp_send_json_error('No report card found.');
    }

    ob_start();
    foreach ($reports as $report) : ?>
        <div class="report-card-entry">
            <p><strong><?php echo esc_html($report->criteria_name); ?>:</strong> 
                <span class="<?php echo esc_attr($report->grade); ?>">
                    <?php echo ucfirst(esc_html($report->grade)); ?>
                </span>
            </p>
            <p>Comments: <?php echo esc_html($report->comments); ?></p>
        </div>
    <?php endforeach;

    wp_send_json_success(ob_get_clean());
}
add_action('wp_ajax_get_report_card', 'get_report_card_callback');
add_action('wp_ajax_nopriv_get_report_card', 'get_report_card_callback');

function enqueue_dashboard_scripts() {
    if (is_page('dashboard')) { // Ensure this script loads only on the dashboard page
        wp_enqueue_script('dashboard-js', get_template_directory_uri() . '/js/dashboard.js', array('jquery'), null, true);
        wp_localize_script('dashboard-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_dashboard_scripts');
