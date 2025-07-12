<?php
// config/email.php - Email configuration
require_once 'vendor/autoload.php';

class EmailService {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'your-email@gmail.com';
    private $smtp_password = 'your-app-password';
    private $from_email = 'your-email@gmail.com';
    private $from_name = 'Task Manager System';

    public function sendEmail($to_email, $to_name, $subject, $body) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtp_port;

            // Recipients
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}
?>