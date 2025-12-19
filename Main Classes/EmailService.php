<?php
require_once __DIR__ . '/../config.php';

// Note: This is a wrapper for PHPMailer. To use, install PHPMailer via composer:
// composer require phpmailer/phpmailer

class EmailService {
    private $mailer;

    public function __construct() {
        // Check if PHPMailer is installed
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            error_log("PHPMailer is not installed. Please run: composer require phpmailer/phpmailer");
            return;
        }

        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USER;
        $this->mailer->Password = SMTP_PASS;
        $this->mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = SMTP_PORT;
        
        // From
        $this->mailer->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    }

    public function sendBookingConfirmation($bookingData, $recipientEmail, $recipientName) {
        try {
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return [
                    'success' => false,
                    'message' => 'PHPMailer not installed'
                ];
            }

            $this->mailer->addAddress($recipientEmail, $recipientName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Booking Confirmation - ' . $bookingData['booking_reference'];
            
            $body = $this->getBookingConfirmationTemplate($bookingData);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Confirmation email sent successfully'
            ];
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }

    private function getBookingConfirmationTemplate($data) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .booking-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Serendip Waves Cruise</h1>
                    <p>Booking Confirmation</p>
                </div>
                <div class="content">
                    <h2>Thank you for your booking!</h2>
                    <p>Your cruise booking has been confirmed. Here are your booking details:</p>
                    
                    <div class="booking-details">
                        <h3>Booking Reference: ' . htmlspecialchars($data['booking_reference']) . '</h3>
                        <p><strong>Itinerary:</strong> ' . htmlspecialchars($data['itinerary_name'] ?? 'N/A') . '</p>
                        <p><strong>Ship:</strong> ' . htmlspecialchars($data['ship_name'] ?? 'N/A') . '</p>
                        <p><strong>Cabin:</strong> ' . htmlspecialchars($data['cabin_number'] ?? 'N/A') . '</p>
                        <p><strong>Check-in Date:</strong> ' . htmlspecialchars($data['check_in_date']) . '</p>
                        <p><strong>Check-out Date:</strong> ' . htmlspecialchars($data['check_out_date']) . '</p>
                        <p><strong>Number of Passengers:</strong> ' . htmlspecialchars($data['number_of_passengers']) . '</p>
                        <p><strong>Total Amount:</strong> $' . htmlspecialchars($data['total_amount']) . '</p>
                        <p><strong>Status:</strong> ' . htmlspecialchars(ucfirst($data['status'])) . '</p>
                    </div>
                    
                    <p>We look forward to welcoming you aboard!</p>
                </div>
                <div class="footer">
                    <p>&copy; 2025 Serendip Waves Cruise. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ';
    }

    public function sendPasswordReset($email, $resetToken) {
        try {
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return [
                    'success' => false,
                    'message' => 'PHPMailer not installed'
                ];
            }

            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request - Serendip Waves';
            
            $resetLink = "https://serendipwaves.com/reset-password?token=" . $resetToken;
            
            $body = '
            <!DOCTYPE html>
            <html>
            <body>
                <h2>Password Reset Request</h2>
                <p>You have requested to reset your password. Click the link below to proceed:</p>
                <p><a href="' . htmlspecialchars($resetLink) . '">Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this, please ignore this email.</p>
            </body>
            </html>
            ';
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Password reset email sent successfully'
            ];
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }
}
?>
