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
