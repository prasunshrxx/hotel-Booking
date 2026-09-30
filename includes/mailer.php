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
    $year      = date('Y');

    // Simple and clean text-focused HTML template
    $html_body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOTUS Verification Code</title>
</head>
<body style="margin: 0; padding: 24px 12px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 520px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 32px 28px; text-align: left;" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
                            <span style="font-size: 18px; font-weight: 700; color: #0f172a; letter-spacing: 1px;">LOTUS HOTEL</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 24px;">
                            <h1 style="font-size: 18px; font-weight: 600; color: #0f172a; margin: 0 0 12px 0;">Verify Your Email</h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
                                Hello {$safe_name},<br>
                                Use the verification code below to verify your account:
                            </p>

                            <div style="background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px; text-align: center; margin: 20px 0;">
                                <span style="font-family: 'Courier New', Courier, monospace; font-size: 30px; font-weight: 700; letter-spacing: 8px; color: #0f172a; display: inline-block;">{$safe_code}</span>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin: 16px 0 0 0;">
                                This code expires in 10 minutes. If you did not request this verification, please disregard this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 24px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #94a3b8; text-align: center;">
                            &copy; {$year} LOTUS Hotel &bull; Kathmandu, Nepal &bull; info@lotushotel.com
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $text_body = "LOTUS HOTEL\n\nHello {$safe_name},\n\nYour verification code is: {$safe_code}\n\nThis code expires in 10 minutes.\nIf you did not request this, please ignore this email.\n\nLOTUS Hotel • Kathmandu, Nepal • info@lotushotel.com";

    return _lotus_send_mail($recipient_email, $recipient_name, $subject, $html_body, $text_body);
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
    $subject    = 'Booking Confirmed – LOTUS Hotel #' . ($booking['booking_id'] ?? '');

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
<body style="margin: 0; padding: 24px 12px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 560px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 32px 28px; text-align: left;" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
                            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <span style="font-size: 18px; font-weight: 700; color: #0f172a; letter-spacing: 1px;">LOTUS HOTEL</span>
                                    </td>
                                    <td align="right" style="font-size: 13px; color: #64748b;">
                                        Ref: #LOTUS{$booking_id}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 24px;">
                            <h1 style="font-size: 18px; font-weight: 600; color: #0f172a; margin: 0 0 10px 0;">Booking Confirmation</h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
                                Hello {$safe_name},<br>
                                Thank you for your reservation. Here are your booking details:
                            </p>

                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; margin-bottom: 20px;">
                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b; width: 40%;">Room</td>
                                    <td style="padding: 10px 16px; font-size: 13px; font-weight: 600; color: #0f172a;">{$safe_room}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b;">Check-in</td>
                                    <td style="padding: 10px 16px; font-size: 13px; color: #0f172a;">{$safe_checkin}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b;">Check-out</td>
                                    <td style="padding: 10px 16px; font-size: 13px; color: #0f172a;">{$safe_checkout}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b;">Guests</td>
                                    <td style="padding: 10px 16px; font-size: 13px; color: #0f172a;">{$safe_guests}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b;">Contact Phone</td>
                                    <td style="padding: 10px 16px; font-size: 13px; color: #0f172a;">{$safe_phone}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b;">Special Request</td>
                                    <td style="padding: 10px 16px; font-size: 13px; color: #0f172a;">{$safe_request}</td>
                                </tr>
                                <tr style="background-color: #f8fafc;">
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #0f172a;">Total Amount</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a;">{$safe_price}</td>
                                </tr>
                            </table>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin: 0 0 8px 0;">
                                <strong>Status:</strong> Your reservation is currently pending review by our team. If you have questions, please write to us at <a href="mailto:info@lotushotel.com" style="color: #0f172a; text-decoration: underline;">info@lotushotel.com</a>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 24px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #94a3b8; text-align: center;">
                            &copy; {$year} LOTUS Hotel &bull; Kathmandu, Nepal &bull; info@lotushotel.com
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $text_body = "LOTUS HOTEL - BOOKING CONFIRMATION\n\nHello {$safe_name},\n\nThank you for your reservation at LOTUS Hotel.\n\nBooking Reference: #LOTUS{$booking_id}\nRoom:             {$safe_room}\nCheck-in:         {$safe_checkin}\nCheck-out:        {$safe_checkout}\nGuests:           {$safe_guests}\nContact Phone:    {$safe_phone}\nSpecial Request:  {$safe_request}\nTotal:            {$safe_price}\n\nStatus: Pending review.\nIf you have questions, please contact us at info@lotushotel.com.\n\nLOTUS Hotel • Kathmandu, Nepal";

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
    $subject    = 'Booking Cancelled – LOTUS Hotel #' . ($booking['booking_id'] ?? '');

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
<body style="margin: 0; padding: 24px 12px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 560px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 32px 28px; text-align: left;" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
                            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <span style="font-size: 18px; font-weight: 700; color: #0f172a; letter-spacing: 1px;">LOTUS HOTEL</span>
                                    </td>
                                    <td align="right" style="font-size: 13px; color: #64748b;">
                                        Ref: #LOTUS{$booking_id}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 24px;">
                            <h1 style="font-size: 18px; font-weight: 600; color: #0f172a; margin: 0 0 10px 0;">Booking Cancellation</h1>
                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
                                Hello {$safe_name},<br>
                                This confirms that your reservation #LOTUS{$booking_id} has been cancelled.
                            </p>

                            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; margin-bottom: 20px;">
                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b; width: 40%;">Room</td>
                                    <td style="padding: 10px 16px; font-size: 13px; font-weight: 600; color: #0f172a;">{$safe_room}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b;">Check-in</td>
                                    <td style="padding: 10px 16px; font-size: 13px; color: #0f172a;">{$safe_checkin}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; font-size: 13px; color: #64748b;">Check-out</td>
                                    <td style="padding: 10px 16px; font-size: 13px; color: #0f172a;">{$safe_checkout}</td>
                                </tr>
                                <tr style="background-color: #f8fafc;">
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #0f172a;">Total Amount</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a;">{$safe_price}</td>
                                </tr>
                            </table>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin: 0;">
                                If you did not request this cancellation or have any questions, please reach us at <a href="mailto:info@lotushotel.com" style="color: #0f172a; text-decoration: underline;">info@lotushotel.com</a>. We hope to welcome you another time.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 24px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #94a3b8; text-align: center;">
                            &copy; {$year} LOTUS Hotel &bull; Kathmandu, Nepal &bull; info@lotushotel.com
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $text_body = "LOTUS HOTEL - BOOKING CANCELLATION\n\nHello {$safe_name},\n\nYour reservation at LOTUS Hotel has been cancelled.\n\nBooking Reference: #LOTUS{$booking_id}\nRoom:             {$safe_room}\nCheck-in:         {$safe_checkin}\nCheck-out:        {$safe_checkout}\nTotal Amount:     {$safe_price}\n\nIf this cancellation was in error or you have any questions, please contact us at info@lotushotel.com.\n\nLOTUS Hotel • Kathmandu, Nepal";

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
