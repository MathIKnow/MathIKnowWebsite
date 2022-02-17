<?php

namespace MathIKnow;

use PHPMailer\PHPMailer\PHPMailer;

class Email {
    public static function createPHPMailer() {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = '';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = '';
        $mail->Password = '';
        $mail->setFrom('noreply@mathiknow.com', 'MathIKnow');
        return $mail;
    }

    public static function sendMail($recipient, $subject, $body) {
        $mail = self::createPHPMailer();
        $mail->addAddress($recipient);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
    }

    public static function sendHTMLMail($recipient, $subject, $body, $altBody = '') {
        $mail = self::createPHPMailer();
        $mail->addAddress($recipient);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->AltBody = $altBody;
        $mail->send();
    }
}