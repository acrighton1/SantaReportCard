<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Ensure Stripe SDK is loaded

use Stripe\Stripe;
use Stripe\Checkout\Session;

// Load environment variables if necessary
$stripe_secret_key = 'your_secret_key_here'; // Replace with your actual Stripe secret key
$stripe_publishable_key = 'STRIPE_API_KEY'; // Replace with your actual Stripe publishable key

Stripe::setApiKey($stripe_secret_key);

/**
 * Create a Stripe checkout session
 */
function create_stripe_checkout_session($membership_type, $promotion_code = null) {
    \Stripe\Stripe::setApiKey('sk_test_51QqQq02Melw1opnFbHe4SAAbuvv8FCtySqEZGJBLZYVil8XpMFRnEK3cQA2IbnT30nqCLqP1K9iApRNl5YLd4CU400Dj8MKHhd');

    // Determine price ID based on membership type
    $price_id = ($membership_type === 'premium') ? 'price_1QqQsD2Melw1opnFkWLZzcDe' : 'price_1QrzvQ2Melw1opnF2nvqQ2gM';

    // Prepare discount array if a promotion code is provided
    $discounts = [];
    if ($promotion_code) {
        try {
            $promo = \Stripe\PromotionCode::retrieve($promotion_code);
            if ($promo && $promo->active) {
                $discounts[] = ['promotion_code' => $promo->id];
            }
        } catch (\Exception $e) {
            error_log('Invalid Promotion Code: ' . $e->getMessage());
        }
    }

    // Create Stripe Checkout session
    $session_data = [
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $price_id,
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => home_url('/payment-success?session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url' => home_url('/payment-cancel'),
    ];

    // Add discounts if applicable
    if (!empty($discounts)) {
        $session_data['discounts'] = $discounts;
    }

    $session = \Stripe\Checkout\Session::create($session_data);

    return $session->url;
}
