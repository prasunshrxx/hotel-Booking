<?php
/**
 * Mail Configuration for LOTUS Hotel Booking
 *
 * Configured with live Gmail SMTP for real OTP email delivery.
 */

return [
    // Use 'smtp' for real internet delivery to Gmail/Outlook
    'driver' => 'smtp',

    // Sender details
    'from_email' => 'ahenstha@gmail.com',
    'from_name'  => 'LOTUS Luxury Hotel',

    // SMTP settings
    'smtp' => [
        'host'       => 'smtp.gmail.com',
        'port'       => 465,
        'encryption' => 'ssl', // Port 465 with SSL
        'username'   => 'ahenstha@gmail.com',
        'password'   => 'trspibzkhnbwrcpv', // 16-character Google App Password
        'timeout'    => 10,
    ],
];
