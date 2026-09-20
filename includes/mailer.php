<?php
/**
 * Mailer Helper for LOTUS Hotel Booking
 * Handles email delivery for OTP verification and notifications.
 */

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function get_mail_config() {
    $config_file = __DIR__ . '/mail_config.php';
    if (file_exists($config_file)) {
        return require $config_file;
    }
    return [
        'driver'     => 'mail',
        'from_email' => 'noreply@lotushotel.com',
        'from_name'  => 'LOTUS Luxury Hotel',
    ];
}

/**
 * Send OTP Verification Email
 *
 * @param string $recipient_email
 * @param string $recipient_name
 * @param string $otp_code
 * @return array ['success' => bool, 'error' => string]
 */
function send_otp_email($recipient_email, $recipient_name, $otp_code) {
    $config     = get_mail_config();
    $driver     = strtolower($config['driver'] ?? 'mail');
    $from_email = $config['from_email'] ?? 'noreply@lotushotel.com';
    $from_name  = $config['from_name'] ?? 'LOTUS Luxury Hotel';
    $subject    = "Your LOTUS Verification Code: {$otp_code}";

    $safe_name = htmlspecialchars($recipient_name ?: 'Valued Guest');
    $safe_code = htmlspecialchars($otp_code);

    // Responsive luxury HTML template
    $html_body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOTUS Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b1120; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #e2e8f0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #0b1120; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 560px; background: #131d33; border: 1px solid rgba(251, 191, 36, 0.25); border-radius: 16px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5); overflow: hidden;" cellspacing="0" cellpadding="0" border="0">
                    <!-- Brand Header -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 32px 24px; border-bottom: 1px solid rgba(251, 191, 36, 0.2);">
                            <span style="color: #fbbf24; font-size: 24px; font-weight: 800; letter-spacing: 4px; display: block;">LOTUS</span>
                            <span style="color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; margin-top: 4px; display: block;">Luxury Hotel &amp; Sanctuary</span>
                        </td>
                    </tr>
                    
                    <!-- Content Area -->
                    <tr>
                        <td style="padding: 36px 32px;">
                            <h1 style="color: #ffffff; font-size: 20px; font-weight: 700; margin: 0 0 12px 0; text-align: center;">Verify Your Email Address</h1>
                            <p style="color: #94a3b8; font-size: 14px; line-height: 1.6; margin: 0 0 24px 0; text-align: center;">
                                Hello <strong style="color: #f1f5f9;">{$safe_name}</strong>, thank you for registering with LOTUS. Please enter the one-time verification code below to activate your account.
                            </p>

                            <!-- OTP Box -->
                            <div style="text-align: center; margin: 28px 0;">
                                <div style="display: inline-block; background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); border-radius: 12px; padding: 16px 36px; box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);">
                                    <span style="font-family: 'Courier New', Courier, monospace, monospace; font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #0b1120; display: block;">{$safe_code}</span>
                                </div>
                            </div>

                            <!-- Expiry notice -->
                            <div style="background: rgba(255, 255, 255, 0.04); border-left: 3px solid #fbbf24; padding: 12px 16px; border-radius: 6px; margin: 24px 0;">
                                <p style="color: #cbd5e1; font-size: 13px; margin: 0; line-height: 1.5;">
                                    ⏱ <strong>Expires in 10 minutes.</strong> If you did not initiate this registration, please disregard this email.
                                </p>
                            </div>

                            <p style="color: #64748b; font-size: 12px; line-height: 1.5; margin: 20px 0 0 0; text-align: center;">
                                For your security, never share this OTP with anyone, including LOTUS staff.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #0d1527; padding: 20px 32px; border-top: 1px solid rgba(255, 255, 255, 0.05); text-align: center;">
                            <p style="color: #64748b; font-size: 11px; margin: 0 0 6px 0;">
                                &copy; " . date('Y') . " LOTUS Hotel. All rights reserved.
                            </p>
                            <p style="color: #475569; font-size: 11px; margin: 0;">
                                Kathmandu, Nepal &bull; info@lotushotel.com
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $text_body = "Hello {$safe_name},\n\nYour LOTUS registration verification code is: {$safe_code}\n\nThis code expires in 10 minutes.\nIf you did not request this, please ignore this email.\n\nLOTUS Hotel & Sanctuary";

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        if ($driver === 'smtp') {
            $smtp = $config['smtp'] ?? [];
            $mail->isSMTP();
            $mail->Host       = $smtp['host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp['username'] ?? '';
            $mail->Password   = $smtp['password'] ?? '';
            $mail->Port       = (int)($smtp['port'] ?? 587);

            $encryption = strtolower($smtp['encryption'] ?? 'tls');
            if ($encryption === 'ssl' || $mail->Port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // In Gmail, the from address should be the authenticated user or configured from_email
            $mailFrom = !empty($smtp['username']) ? $smtp['username'] : $from_email;
            $mail->setFrom($mailFrom, $from_name);
            $mail->addReplyTo($mailFrom, $from_name);
        } else {
            // Default 'mail' driver (Laragon intercepts via Mailpit)
            $mail->isMail();
            $mail->setFrom($from_email, $from_name);
            $mail->addReplyTo($from_email, $from_name);
        }

        $mail->addAddress($recipient_email, $recipient_name ?: 'Guest');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = $text_body;

        $sent = $mail->send();
        if ($sent) {
            return ['success' => true, 'driver' => $driver];
        } else {
            return ['success' => false, 'error' => $mail->ErrorInfo, 'driver' => $driver];
        }
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage(), 'driver' => $driver];
    }
}

/**
 * Send Booking Confirmation Email
 *
 * @param string $recipient_email
 * @param string $recipient_name
 * @param array  $booking  Keys: booking_id, room_label, checkin_date, checkout_date, no_of_guests, tprice, phone_number, special_request
 * @return array ['success' => bool, 'error' => string]
 */
function send_booking_email($recipient_email, $recipient_name, $booking) {
    $config     = get_mail_config();
    $driver     = strtolower($config['driver'] ?? 'mail');
    $from_email = $config['from_email'] ?? 'noreply@lotushotel.com';
    $from_name  = $config['from_name']  ?? 'LOTUS Luxury Hotel';
    $subject    = 'Booking Confirmed – LOTUS Luxury Hotel #' . ($booking['booking_id'] ?? '');

    $safe_name    = htmlspecialchars($recipient_name ?: 'Valued Guest');
    $safe_room    = htmlspecialchars($booking['room_label']       ?? 'Room');
    $safe_checkin = htmlspecialchars($booking['checkin_date']     ?? '-');
    $safe_checkout= htmlspecialchars($booking['checkout_date']    ?? '-');
    $safe_guests  = htmlspecialchars($booking['no_of_guests']     ?? '1');
    $safe_price   = 'Rs. ' . number_format((float)($booking['tprice'] ?? 0), 2);
    $safe_phone   = htmlspecialchars($booking['phone_number']     ?? '-');
    $safe_request = htmlspecialchars($booking['special_request']  ?? 'None');
    $booking_id   = intval($booking['booking_id'] ?? 0);
    $year         = date('Y');

    $html_body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed – LOTUS</title>
</head>
<body style="margin:0;padding:0;background-color:#0b1120;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#e2e8f0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0b1120;padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:580px;background:#131d33;border:1px solid rgba(251,191,36,0.25);border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,0.5);overflow:hidden;" cellspacing="0" cellpadding="0" border="0">
                    <!-- Brand Header -->
                    <tr>
                        <td align="center" style="background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);padding:32px 24px;border-bottom:1px solid rgba(251,191,36,0.2);">
                            <span style="color:#fbbf24;font-size:24px;font-weight:800;letter-spacing:4px;display:block;">LOTUS</span>
                            <span style="color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin-top:4px;display:block;">Luxury Hotel &amp; Sanctuary</span>
                        </td>
                    </tr>
                    <!-- Status Badge -->
                    <tr>
                        <td align="center" style="padding:28px 32px 0;">
                            <div style="display:inline-block;background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);border-radius:50px;padding:8px 24px;margin-bottom:16px;">
                                <span style="color:#fff;font-size:13px;font-weight:700;letter-spacing:1px;">✔ BOOKING CONFIRMED</span>
                            </div>
                            <h1 style="color:#ffffff;font-size:22px;font-weight:700;margin:12px 0 6px;text-align:center;">Your Reservation is Confirmed!</h1>
                            <p style="color:#94a3b8;font-size:14px;margin:0 0 4px;text-align:center;">Hello <strong style="color:#f1f5f9;">{$safe_name}</strong>, we're delighted to welcome you.</p>
                            <p style="color:#64748b;font-size:12px;margin:0;text-align:center;">Booking Reference: <strong style="color:#fbbf24;">#LOTUS{$booking_id}</strong></p>
                        </td>
                    </tr>
                    <!-- Booking Details -->
                    <tr>
                        <td style="padding:24px 32px;">
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:rgba(255,255,255,0.04);border:1px solid rgba(251,191,36,0.15);border-radius:12px;overflow:hidden;">
                                <tr>
                                    <td colspan="2" style="background:rgba(251,191,36,0.1);padding:12px 20px;border-bottom:1px solid rgba(251,191,36,0.15);">
                                        <span style="color:#fbbf24;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Booking Details</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);width:45%;">Room</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_room}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">Check-in</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_checkin}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">Check-out</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_checkout}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">Guests</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_guests}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">Contact Phone</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_phone}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">Special Request</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_request}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;color:#94a3b8;font-size:13px;">Total Amount</td>
                                    <td style="padding:14px 20px;color:#fbbf24;font-size:16px;font-weight:800;">{$safe_price}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Note -->
                    <tr>
                        <td style="padding:0 32px 28px;">
                            <div style="background:rgba(255,255,255,0.04);border-left:3px solid #22c55e;padding:12px 16px;border-radius:6px;">
                                <p style="color:#cbd5e1;font-size:13px;margin:0;line-height:1.6;">
                                    Your booking status is currently <strong>pending</strong> and will be reviewed by our team. You will receive a confirmation once it is approved. For any questions, contact us at <a href="mailto:info@lotushotel.com" style="color:#fbbf24;">info@lotushotel.com</a>.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#0d1527;padding:20px 32px;border-top:1px solid rgba(255,255,255,0.05);text-align:center;">
                            <p style="color:#64748b;font-size:11px;margin:0 0 4px;">&copy; {$year} LOTUS Hotel. All rights reserved.</p>
                            <p style="color:#475569;font-size:11px;margin:0;">Kathmandu, Nepal &bull; info@lotushotel.com</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $text_body = "Hello {$safe_name},\n\nYour booking at LOTUS Hotel has been received and is currently pending review.\n\nBooking Reference: #LOTUS{$booking_id}\nRoom: {$safe_room}\nCheck-in:  {$safe_checkin}\nCheck-out: {$safe_checkout}\nGuests:    {$safe_guests}\nTotal:     {$safe_price}\n\nWe will confirm your booking shortly.\n\nLOTUS Hotel & Sanctuary\nKathmandu, Nepal";

    return _lotus_send_mail($recipient_email, $recipient_name, $subject, $html_body, $text_body);
}

/**
 * Send Booking Cancellation Email
 *
 * @param string $recipient_email
 * @param string $recipient_name
 * @param array  $booking  Keys: booking_id, room_label, checkin_date, checkout_date, tprice
 * @return array ['success' => bool, 'error' => string]
 */
function send_cancellation_email($recipient_email, $recipient_name, $booking) {
    $config     = get_mail_config();
    $driver     = strtolower($config['driver'] ?? 'mail');
    $from_email = $config['from_email'] ?? 'noreply@lotushotel.com';
    $from_name  = $config['from_name']  ?? 'LOTUS Luxury Hotel';
    $subject    = 'Booking Cancelled – LOTUS Luxury Hotel #' . ($booking['booking_id'] ?? '');

    $safe_name    = htmlspecialchars($recipient_name ?: 'Valued Guest');
    $safe_room    = htmlspecialchars($booking['room_label']    ?? 'Room');
    $safe_checkin = htmlspecialchars($booking['checkin_date']  ?? '-');
    $safe_checkout= htmlspecialchars($booking['checkout_date'] ?? '-');
    $safe_price   = 'Rs. ' . number_format((float)($booking['tprice'] ?? 0), 2);
    $booking_id   = intval($booking['booking_id'] ?? 0);
    $year         = date('Y');

    $html_body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancelled – LOTUS</title>
</head>
<body style="margin:0;padding:0;background-color:#0b1120;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#e2e8f0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0b1120;padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:580px;background:#131d33;border:1px solid rgba(239,68,68,0.3);border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,0.5);overflow:hidden;" cellspacing="0" cellpadding="0" border="0">
                    <!-- Brand Header -->
                    <tr>
                        <td align="center" style="background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);padding:32px 24px;border-bottom:1px solid rgba(239,68,68,0.2);">
                            <span style="color:#fbbf24;font-size:24px;font-weight:800;letter-spacing:4px;display:block;">LOTUS</span>
                            <span style="color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin-top:4px;display:block;">Luxury Hotel &amp; Sanctuary</span>
                        </td>
                    </tr>
                    <!-- Status Badge -->
                    <tr>
                        <td align="center" style="padding:28px 32px 0;">
                            <div style="display:inline-block;background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);border-radius:50px;padding:8px 24px;margin-bottom:16px;">
                                <span style="color:#fff;font-size:13px;font-weight:700;letter-spacing:1px;">✕ BOOKING CANCELLED</span>
                            </div>
                            <h1 style="color:#ffffff;font-size:22px;font-weight:700;margin:12px 0 6px;text-align:center;">Your Booking Has Been Cancelled</h1>
                            <p style="color:#94a3b8;font-size:14px;margin:0 0 4px;text-align:center;">Hello <strong style="color:#f1f5f9;">{$safe_name}</strong>, your reservation has been cancelled.</p>
                            <p style="color:#64748b;font-size:12px;margin:0;text-align:center;">Booking Reference: <strong style="color:#fbbf24;">#LOTUS{$booking_id}</strong></p>
                        </td>
                    </tr>
                    <!-- Booking Details -->
                    <tr>
                        <td style="padding:24px 32px;">
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:rgba(255,255,255,0.04);border:1px solid rgba(239,68,68,0.15);border-radius:12px;overflow:hidden;">
                                <tr>
                                    <td colspan="2" style="background:rgba(239,68,68,0.1);padding:12px 20px;border-bottom:1px solid rgba(239,68,68,0.15);">
                                        <span style="color:#f87171;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Cancelled Reservation</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);width:45%;">Room</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_room}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">Check-in</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_checkin}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;color:#94a3b8;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">Check-out</td>
                                    <td style="padding:12px 20px;color:#f1f5f9;font-size:13px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05);">{$safe_checkout}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 20px;color:#94a3b8;font-size:13px;">Total Amount</td>
                                    <td style="padding:14px 20px;color:#f87171;font-size:16px;font-weight:800;">{$safe_price}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Note -->
                    <tr>
                        <td style="padding:0 32px 28px;">
                            <div style="background:rgba(255,255,255,0.04);border-left:3px solid #ef4444;padding:12px 16px;border-radius:6px;">
                                <p style="color:#cbd5e1;font-size:13px;margin:0;line-height:1.6;">
                                    If you did not request this cancellation or have any questions, please contact us at <a href="mailto:info@lotushotel.com" style="color:#fbbf24;">info@lotushotel.com</a>. We hope to welcome you again soon.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#0d1527;padding:20px 32px;border-top:1px solid rgba(255,255,255,0.05);text-align:center;">
                            <p style="color:#64748b;font-size:11px;margin:0 0 4px;">&copy; {$year} LOTUS Hotel. All rights reserved.</p>
                            <p style="color:#475569;font-size:11px;margin:0;">Kathmandu, Nepal &bull; info@lotushotel.com</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $text_body = "Hello {$safe_name},\n\nYour booking at LOTUS Hotel has been cancelled.\n\nBooking Reference: #LOTUS{$booking_id}\nRoom: {$safe_room}\nCheck-in:  {$safe_checkin}\nCheck-out: {$safe_checkout}\nTotal:     {$safe_price}\n\nIf this was a mistake or you have questions, please contact us at info@lotushotel.com.\n\nLOTUS Hotel & Sanctuary\nKathmandu, Nepal";

    return _lotus_send_mail($recipient_email, $recipient_name, $subject, $html_body, $text_body);
}

/**
 * Internal helper: configure PHPMailer and send a message.
 */
function _lotus_send_mail($recipient_email, $recipient_name, $subject, $html_body, $text_body) {
    $config     = get_mail_config();
    $driver     = strtolower($config['driver'] ?? 'mail');
    $from_email = $config['from_email'] ?? 'noreply@lotushotel.com';
    $from_name  = $config['from_name']  ?? 'LOTUS Luxury Hotel';

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        if ($driver === 'smtp') {
            $smtp = $config['smtp'] ?? [];
            $mail->isSMTP();
            $mail->Host     = $smtp['host']     ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $smtp['username'] ?? '';
            $mail->Password = $smtp['password'] ?? '';
            $mail->Port     = (int)($smtp['port'] ?? 587);

            $encryption = strtolower($smtp['encryption'] ?? 'tls');
            if ($encryption === 'ssl' || $mail->Port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mailFrom = !empty($smtp['username']) ? $smtp['username'] : $from_email;
            $mail->setFrom($mailFrom, $from_name);
            $mail->addReplyTo($mailFrom, $from_name);
        } else {
            $mail->isMail();
            $mail->setFrom($from_email, $from_name);
            $mail->addReplyTo($from_email, $from_name);
        }

        $mail->addAddress($recipient_email, $recipient_name ?: 'Guest');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = $text_body;

        $sent = $mail->send();
        return $sent
            ? ['success' => true,  'driver' => $driver]
            : ['success' => false, 'error'  => $mail->ErrorInfo, 'driver' => $driver];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage(), 'driver' => $driver];
    }
}
