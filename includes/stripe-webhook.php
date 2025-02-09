<?php

require_once 'includes/stripe-functions.php';

function handle_stripe_webhook() {
    $endpoint_secret = 'your_stripe_endpoint_secret'; // Set this in Stripe Dashboard

    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    
    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    } catch (\UnexpectedValueException $e) {
        http_response_code(400); // Invalid payload
        exit();
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        http_response_code(400); // Invalid signature
        exit();
    }

    // Handle the event
    switch ($event->type) {
        case 'invoice.payment_succeeded':
            // Update your database to mark the subscription as paid
            $subscription = $event->data->object; // Contains a \Stripe\Invoice object
            $user_id = get_user_id_by_stripe_customer_id($subscription->customer);
            update_subscription_status($user_id, 'active');
            break;
        
        case 'customer.subscription.updated':
            // Handle subscription updates (e.g., switching plans)
            break;
        
        case 'customer.subscription.deleted':
            // Handle subscription cancellations
            break;

        default:
            // Unexpected event type
            http_response_code(200); // Acknowledge receipt of the event
            exit();
    }

    http_response_code(200);
}
add_action('init', 'handle_stripe_webhook');
