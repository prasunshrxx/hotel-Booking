<?php
/**
 * Khalti Payment Gateway Configuration for LOTUS Hotel Booking
 *
 * Sandbox / Testing Environment:
 * - Dashboard: https://test-admin.khalti.com
 * - Documentation: https://docs.khalti.com/khalti-epayment/
 * - To use your own Khalti Sandbox merchant account, sign up at test-admin.khalti.com,
 *   copy your "live_secret_key" from API Keys, and paste it into 'secret_key' below.
 *
 * Official Sandbox Test Credentials (on Khalti payment page):
 * - Mobile / Khalti ID: 9800000000 to 9800000005
 * - MPIN: 1111
 * - OTP: 987654
 */

return [
    // Mode: 'sandbox' or 'live'
    'mode' => 'sandbox',

    // Sandbox API Configuration
    'sandbox' => [
        'base_url'              => 'https://dev.khalti.com/api/v2/',
        'initiate_url'          => 'https://dev.khalti.com/api/v2/epayment/initiate/',
        'lookup_url'            => 'https://dev.khalti.com/api/v2/epayment/lookup/',
        // Your Khalti Sandbox Secret Key from https://test-admin.khalti.com
        'secret_key'            => 'live_secret_key_6821c420119d4ad38b825a40a013b52f',
        'public_key'            => 'live_public_key_6821c420119d4ad38b825a40a013b52f',
        // Fallback to local sandbox simulator if the remote test key returns 401 / unconfigured
        'mock_sandbox_fallback' => true,
    ],

    // Production API Configuration (for live transactions)
    'live' => [
        'base_url'              => 'https://khalti.com/api/v2/',
        'initiate_url'          => 'https://khalti.com/api/v2/epayment/initiate/',
        'lookup_url'            => 'https://khalti.com/api/v2/epayment/lookup/',
        'secret_key'            => '',
        'public_key'            => '',
        'mock_sandbox_fallback' => false,
    ],
];
