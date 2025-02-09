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
// Add this to stripe-functions.php
function create_stripe_checkout_session($membership_type) {
    \Stripe\Stripe::setApiKey('sk_test_51QqQq02Melw1opnFbHe4SAAbuvv8FCtySqEZGJBLZYVil8XpMFRnEK3cQA2IbnT30nqCLqP1K9iApRNl5YLd4CU400Dj8MKHhd'); // Use your secret Stripe key

    // Determine price ID based on membership type
    $price_id = $membership_type === 'premium' ? 'price_1QqQsD2Melw1opnFkWLZzcDe' : 'basic_price_id';

    // Create a Stripe Checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price' => $price_id,
                'quantity' => 1,
            ],
        ],
        'mode' => 'subscription', // Use 'payment' for one-time payments
        'success_url' => home_url('/payment-success?session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url' => home_url('/payment-cancel'),
    ]);

    return $session->url;
}
