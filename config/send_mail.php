<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../mail/PHPMailer/src/Exception.php';
require '../mail/PHPMailer/src/PHPMailer.php';
require '../mail/PHPMailer/src/SMTP.php';

function sendResetCode($email, $code, $firstName, $lastName)
{
    $mail = new PHPMailer(true);

    try {

        // SMTP CONFIG
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'vitaldrop123@gmail.com';

        // Gmail App Password 
        $mail->Password = 'sbdo lceo dnfq imbi';

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // EMAIL SETTINGS
        $mail->setFrom('vitaldrop123@gmail.com', 'Vital Drop');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'VitalDrop Password Reset Code';

        // Safe name
        $fullName = htmlspecialchars($firstName . ' ' . $lastName);

        // EMBED LOGO 
        $mail->addEmbeddedImage('../images/logo.png', 'logo_cid');

        // EMAIL BODY
        $mail->Body = "
<div style='font-family:Arial;padding:20px;max-width:600px;margin:auto'>

    <h2 style='text-align:center;'>Vital Drop</h2>

    <p>Dear <b>$fullName</b>,</p>

    <p>Your password reset code is:</p>

    <div style='font-size:24px;font-weight:bold;text-align:center;
        background:#f0f6ff;padding:10px;border-radius:8px;'>
        $code
    </div>

    <p>Please use this code to reset your password. It will expire soon.</p>
    <p style='color:red;'>Do not share this code with anyone.</p>
    <p>Best Regards,<br>Vital Drop Team</p>
    <img src='cid:logo_cid' style='width:120px;margin-top:10px'>

</div>
";

        // FALLBACK TEXT EMAIL
        $mail->AltBody =
            "Dear $fullName,\n\n" .
            "Your password reset code is: $code\n\n" .
            "This code will expire soon.\n\n" .
            "Best Regards,\nVital Drop Team";

        // SEND EMAIL
        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    }
}