<?php

// Include WordPress functions
require_once(ABSPATH . 'wp-load.php');
require_once(ABSPATH . 'wp-includes/pluggable.php');

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
    comments VARCHAR(1000),
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

// Handle parent logout
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
        wp_redirect(site_url('/thank-you'));
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

        // Insert a single row with all grades
        $wpdb->insert("{$wpdb->prefix}report_cards", [
            'kid_id' => $kid_id,
            "report_date" => $report_date, // FIX: Now defined properly
            'criteria_id' => $criteria->id,
            'grade_1' => sanitize_text_field($_POST['criteria_1']),
            'grade_2' => sanitize_text_field($_POST['criteria_2']),
            'grade_3' => sanitize_text_field($_POST['criteria_3']),
            'grade_4' => sanitize_text_field($_POST['criteria_4']),
            'grade_5' => sanitize_text_field($_POST['criteria_5']),
            'comments' => $comments,
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
        <input type="date" name="report_date" id="report_date" required>

        <h4>Grading Criteria</h4>
        <div id="grading-criteria"></div>

        <label for="santa_comments">Santa's Comments</label>
        <textarea name="santa_comments" id="santa_comments" required></textarea>

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
                    criteriaDiv.innerHTML += `
                    <label>${data['criteria_' + i]}</label>
                    <select name="criteria_${i}" required>
                        <option value="green" style="background-color: #008000; color: white;">Green</option>
                        <option value="yellow" style="background-color: #FFD700; color: black;">Yellow</option>
                        <option value="red" style="background-color: #FF0000; color: white;">Red</option>
                    </select>
                `;
                }
            });
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
// Display Report Cards on Dashboard with View & Print Button
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
        echo '<thead><tr><th>Report Date</th><th>View & Print</th></tr></thead><tbody>';

        foreach ($report_cards as $report) {
            $report_date = !empty($report->report_date) ? esc_html($report->report_date) : 'N/A';

            echo "<tr>
                <td>{$report_date}</td>
                <td><a href='" . esc_url(get_permalink(get_page_by_path('report-card-view')) . "?report_id={$report->report_id}") . "' target='_blank' class='button'>View & Print</a></td>
            </tr>";
        }

        echo '</tbody></table>';
    }

    return ob_get_clean();
}
add_shortcode('display_report_cards', 'display_report_cards');



// Display Report Card Data
function inject_report_card_data() {
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

    // Get the previous month's report card (if it exists)
    $previous_month_date = date('Y-m-d', strtotime('-0 month', strtotime($current_report->report_date)));
    $previous_report = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}report_cards WHERE kid_id = %d AND report_date = %s", $current_report->kid_id, $previous_month_date));

    // Fetch the child and grading criteria data
    $kid = $wpdb->get_row($wpdb->prepare("SELECT full_name FROM {$wpdb->prefix}kids WHERE kid_id = %d", $current_report->kid_id));
    $criteria = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}grading_criteria WHERE id = %d", $current_report->criteria_id));

    if (!$criteria) {
        echo '<script>document.querySelector(".wp-block-group").innerHTML = "<p>No grading criteria found.</p>";</script>';
        return;
    }

    // Define grade mappings
    $grade_values = ["green" => 3, "yellow" => 2, "red" => 1];
    $grade_colors = ["green" => "#008000", "yellow" => "#FFD700", "red" => "#FF0000"];

    function build_criteria_html($report, $criteria, $grade_values, $grade_colors) {
        $criteria_html = "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 10px;'>";

        for ($i = 1; $i <= 5; $i++) {
            $criteria_name = isset($criteria->{"criteria_$i"}) ? esc_html($criteria->{"criteria_$i"}) : "Unnamed Criteria";
            $grade = strtolower(trim(esc_html($report->{"grade_$i"}))); 
            $color = isset($grade_colors[$grade]) ? $grade_colors[$grade] : "#000000"; 

            $criteria_html .= "<div><strong>{$criteria_name}:</strong></div>
                <div style='padding: 5px 10px; background-color: {$color}; color: white; font-weight: bold; border-radius: 5px;'>
                    {$grade}
                </div>";
        }

        $criteria_html .= "</div>";
        return $criteria_html;
    }

    function build_average_criteria_html($current_report, $previous_report, $criteria, $grade_values, $grade_colors) {
        $average_html = "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 10px;'>";

        for ($i = 1; $i <= 5; $i++) {
            $criteria_name = isset($criteria->{"criteria_$i"}) ? esc_html($criteria->{"criteria_$i"}) : "Unnamed Criteria";

            // Get current and previous grades
            $current_grade = strtolower(trim(esc_html($current_report->{"grade_$i"})));
            $previous_grade = $previous_report ? strtolower(trim(esc_html($previous_report->{"grade_$i"}))) : null;

            // Convert grades to numeric values
            $current_value = $grade_values[$current_grade] ?? null;
            $previous_value = $previous_grade ? ($grade_values[$previous_grade] ?? null) : null;

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
            $average_grade = array_search($average_value, $grade_values);
            $color = $average_grade ? $grade_colors[$average_grade] : "#000000";

            $average_html .= "<div><strong>{$criteria_name}:</strong></div>
                <div style='padding: 5px 10px; background-color: {$color}; color: white; font-weight: bold; border-radius: 5px;'>
                    " . ($average_grade ? strtoupper($average_grade) : "N/A") . "
                </div>";
        }

        $average_html .= "</div>";
        return $average_html;
    }

    // Generate HTML for each section
    $current_criteria_html = build_criteria_html($current_report, $criteria, $grade_values, $grade_colors);
    $previous_criteria_html = $previous_report ? build_criteria_html($previous_report, $criteria, $grade_values, $grade_colors) : '<p>No previous report card available.</p>';
    $average_criteria_html = build_average_criteria_html($current_report, $previous_report, $criteria, $grade_values, $grade_colors);

    // Inject data into the page
    echo "<script>
        document.querySelector('.child-name').textContent = '" . esc_js($kid->full_name) . "';
        document.querySelector('.report-date').textContent = '" . esc_js($current_report->report_date) . "';
        document.querySelector('.report-comments').innerHTML = '" . nl2br(esc_js($current_report->comments)) . "';
        
        // Inject Current Month Grades
        document.querySelector('.current-criteria').innerHTML = '<h4>Current Month Grades</h4>' + `${current_criteria_html}`;
        
        // Inject Previous Month Grades
        document.querySelector('.previous-criteria').innerHTML = '<h4>Previous Month Grades</h4>' + `${previous_criteria_html}`;
        
        // Inject Average Grades for Each Criterion
        document.querySelector('.average-criteria').innerHTML = '<h4>Average Grades</h4>' + `${average_criteria_html}`;
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
            <li><a href="#">🏠 Home</a></li>
            <li><a href="/childregister">👥 Register Child</a></li>
            <li><a href="#">📊 Report Cards</a></li>
            <li><a href="#">⚙️ Membership</a></li>
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



function enqueue_dashboard_scripts()
{
    if (is_page('dashboard')) { // Ensure this script loads only on the dashboard page
        wp_enqueue_script('dashboard-js', get_template_directory_uri() . '/js/dashboard.js', array('jquery'), null, true);
        wp_localize_script('dashboard-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_dashboard_scripts');
