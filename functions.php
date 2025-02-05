<?php

/**
 * Twenty Twenty-Five Child Theme functions and definitions
 *
 * @package twentytwentyfive-child
 */

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

// Start a session if not already started
if (!session_id()) {
    session_start();
}

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

// Handle parent logout
function handle_parent_logout()
{
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        // Clear the session
        session_unset();
        session_destroy();
        wp_redirect(home_url('/login'));
        exit;
    }
}
add_action('init', 'handle_parent_logout');

// Handle parent registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
    $full_name = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : '';
    $referral_code = isset($_POST['referral_code']) ? sanitize_text_field($_POST['referral_code']) : '';
    $membership_type = isset($_POST['membership_type']) ? sanitize_text_field($_POST['membership_type']) : '';

    if (!empty($email) && !empty($password) && !empty($full_name)) {
        global $wpdb;
        $inserted = $wpdb->insert(
            "{$wpdb->prefix}parents",
            [
                'email' => $email,
                'password_hash' => wp_hash_password($password),
                'full_name' => $full_name,
                'referral_code' => $referral_code,
                'membership_type' => $membership_type,
                'created_at' => current_time('mysql')
            ]
        );

        // Redirect to Thank You page
        wp_redirect(site_url('/thank-you'));
        exit;
    }
}


// Handle child registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    global $wpdb;

    $full_name = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : '';
    $parent_email = $_SESSION['parent_email'];

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
        wp_redirect(site_url('/thank-you'));
        exit;
    }
}



function src_child_registration_form() {
    if (!isset($_SESSION['parent_email'])) {
        return '<p>You must be logged in to register a child.</p>';
    }

    ob_start(); ?>
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
                    <input type="text" name="criteria_<?php echo $i; ?>" id="criteria_<?php echo $i; ?>" required placeholder="Enter criteria">
                </div>
            </div>
        <?php endfor; ?>

        <div class="wp-block-columns">
            <div class="wp-block-column">
                <button type="submit" class="wp-block-button__link">Register Child</button>
            </div>
        </div>
    </form>

    <style>
        .child-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 20px auto;
        }
        .child-form label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .child-form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        .child-form button {
            background: #0073aa;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            text-align: center;
            transition: 0.3s;
        }
        .child-form button:hover {
            background: #005a87;
        }
    </style>

    <?php return ob_get_clean();
}
add_shortcode('child_registration_form', 'src_child_registration_form');


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
    $output .= '<a class="wp-block-button__link" href="#">Generate Report Card</a>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}
add_shortcode('dashboard_overview', 'get_dashboard_overview');

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

//Dynamically fetch Children in Drop down and Associated Report Card
function get_child_dropdown()
{
    if (!isset($_SESSION['parent_email'])) {
        return '<p>Please log in to manage your kids.</p>';
    }

    global $wpdb;
    $parent_email = $_SESSION['parent_email'];

    // Fetch parent_id
    $parent = $wpdb->get_row($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->prefix}parents WHERE email = %s",
        $parent_email
    ));

    if (!$parent) {
        return '<p>Parent not found.</p>';
    }

    // Fetch children associated with this parent
    $kids = $wpdb->get_results($wpdb->prepare(
        "SELECT kid_id, full_name FROM {$wpdb->prefix}kids WHERE parent_id = %d",
        $parent->user_id
    ));

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


function get_report_card_callback()
{
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

function enqueue_dashboard_scripts()
{
    if (is_page('dashboard')) { // Ensure this script loads only on the dashboard page
        wp_enqueue_script('dashboard-js', get_template_directory_uri() . '/js/dashboard.js', array('jquery'), null, true);
        wp_localize_script('dashboard-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_dashboard_scripts');
