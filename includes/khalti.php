<?php
/**
 * Khalti Payment Gateway Helper for LOTUS Hotel Booking
 *
 * Implements Khalti ePayment v2 API (Sandbox & Production)
 * - Payment Initiation: /api/v2/epayment/initiate/
 * - Payment Verification (Lookup): /api/v2/epayment/lookup/
 */

require_once __DIR__ . '/../conn.php';

function get_khalti_config() {
    $config_file = __DIR__ . '/khalti_config.php';
    if (file_exists($config_file)) {
        return require $config_file;
    }
    return [
        'mode' => 'sandbox',
        'sandbox' => [
            'base_url'              => 'https://dev.khalti.com/api/v2/',
            'initiate_url'          => 'https://dev.khalti.com/api/v2/epayment/initiate/',
            'lookup_url'            => 'https://dev.khalti.com/api/v2/epayment/lookup/',
            'secret_key'            => '',
            'public_key'            => '',
            'mock_sandbox_fallback' => true,
        ],
    ];
}

function get_active_khalti_env() {
    $cfg = get_khalti_config();
    $mode = strtolower($cfg['mode'] ?? 'sandbox');
    return $cfg[$mode] ?? $cfg['sandbox'];
}

function get_hotel_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';

    // If served via Apache virtual host (e.g. hotelbooking.test)
    if (strpos($host, 'hotelbooking') !== false && strpos($script, '/hotelbooking') === false) {
        return rtrim($protocol . $host, '/');
    }

    // If served under /hotelbooking subfolder
    if (strpos($script, '/hotelbooking') !== false) {
        return rtrim($protocol . $host . '/hotelbooking', '/');
    }

    // Default for Laragon standard localhost access
    if ($host === 'localhost' || $host === '127.0.0.1') {
        return rtrim($protocol . $host . '/hotelbooking', '/');
    }

    return rtrim($protocol . $host, '/');
}

/**
 * Initiate Payment via Khalti ePayment v2 API
 *
 * @param int    $booking_id
 * @param float  $amount_npr
 * @param string $room_label
 * @param string $guest_name
 * @param string $guest_email
 * @param string $guest_phone
 * @return array ['success' => bool, 'pidx' => string, 'payment_url' => string, 'error' => string, 'is_simulator' => bool]
 */
function khalti_initiate_payment($booking_id, $amount_npr, $room_label, $guest_name, $guest_email, $guest_phone) {
    $env = get_active_khalti_env();
    $secret_key = trim($env['secret_key'] ?? '');
    $initiate_url = $env['initiate_url'] ?? 'https://dev.khalti.com/api/v2/epayment/initiate/';
    $allow_mock_fallback = !empty($env['mock_sandbox_fallback']);

    $base_url = get_hotel_base_url();
    $return_url = $base_url . '/pages/khalti_callback.php';
    $website_url = $base_url . '/index.php';

    // Amount in paisa (1 NPR = 100 paisa)
    $amount_paisa = intval(round(floatval($amount_npr) * 100));
    if ($amount_paisa < 1000) {
        // Minimum amount for Khalti is 1000 paisa (Rs. 10)
        $amount_paisa = 1000;
    }

    $payload = [
        'return_url'          => $return_url,
        'website_url'         => $website_url,
        'amount'              => $amount_paisa,
        'purchase_order_id'   => 'LOTUS-' . $booking_id,
        'purchase_order_name' => 'Reservation: ' . substr($room_label, 0, 80),
        'customer_info'       => [
            'name'  => $guest_name ?: 'Valued Guest',
            'email' => $guest_email ?: 'guest@example.com',
            'phone' => preg_replace('/[^0-9]/', '', $guest_phone) ?: '9800000000',
        ],
    ];

    // Attempt real Khalti API call if secret key is configured
    if (!empty($secret_key)) {
        $ch = curl_init($initiate_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Key ' . $secret_key,
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if (!$curl_err && $http_code >= 200 && $http_code < 300) {
            $data = json_decode($response, true);
            if (!empty($data['pidx']) && !empty($data['payment_url'])) {
                return [
                    'success'      => true,
                    'pidx'         => $data['pidx'],
                    'payment_url'  => $data['payment_url'],
                    'is_simulator' => false,
                ];
            }
        }

        // If the API failed and mock fallback is not enabled, return the error
        if (!$allow_mock_fallback) {
            return [
                'success' => false,
                'error'   => "Khalti API error (HTTP {$http_code}): " . ($response ?: $curl_err),
            ];
        }
    }

    // Fallback: Local Sandbox Simulator for instant testing
    if ($allow_mock_fallback) {
        $mock_pidx = 'mock_pidx_' . bin2hex(random_bytes(10)) . '_' . $booking_id;
        $sim_url = $base_url . '/pages/khalti_simulator.php?pidx=' . urlencode($mock_pidx) . '&booking_id=' . urlencode($booking_id) . '&amount=' . urlencode($amount_paisa);

        return [
            'success'      => true,
            'pidx'         => $mock_pidx,
            'payment_url'  => $sim_url,
            'is_simulator' => true,
        ];
    }

    return [
        'success' => false,
        'error'   => 'Khalti secret key is missing. Please configure it in includes/khalti_config.php.',
    ];
}

/**
 * Verify Payment via Khalti Lookup API
 *
 * @param string $pidx
 * @return array ['success' => bool, 'status' => string, 'transaction_id' => string, 'amount' => int, 'raw' => array]
 */
function khalti_verify_payment($pidx) {
    if (empty($pidx)) {
        return ['success' => false, 'error' => 'Missing payment identifier (pidx).'];
    }

    // Handle Mock Sandbox verification
    if (strpos($pidx, 'mock_pidx_') === 0) {
        return [
            'success'        => true,
            'status'         => 'Completed',
            'transaction_id' => 'KHLT-MOCK-' . strtoupper(substr(md5($pidx), 0, 8)),
            'amount'         => 0,
            'raw'            => ['status' => 'Completed', 'mock' => true],
        ];
    }

    $env = get_active_khalti_env();
    $secret_key = trim($env['secret_key'] ?? '');
    $lookup_url = $env['lookup_url'] ?? 'https://dev.khalti.com/api/v2/epayment/lookup/';

    $ch = curl_init($lookup_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['pidx' => $pidx]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Key ' . $secret_key,
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        return ['success' => false, 'error' => 'cURL Error: ' . $curl_err];
    }

    $result = json_decode($response, true);
    $status = $result['status'] ?? 'Unknown';

    if ($http_code >= 200 && $http_code < 300 && $status === 'Completed') {
        return [
            'success'        => true,
            'status'         => 'Completed',
            'transaction_id' => $result['transaction_id'] ?? ($result['tidx'] ?? ('KHLT-' . substr($pidx, 0, 10))),
            'amount'         => $result['total_amount'] ?? ($result['amount'] ?? 0),
            'raw'            => $result,
        ];
    }

    return [
        'success' => false,
        'status'  => $status,
        'error'   => $result['detail'] ?? ($result['message'] ?? "Khalti lookup status: {$status}"),
        'raw'     => $result,
    ];
}
