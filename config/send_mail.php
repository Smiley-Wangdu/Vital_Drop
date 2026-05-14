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

function sendDonorContactMail($donorEmail, $donorName, $requesterName, $bloodGroup, $requesterEmail, $requesterPhone, $requesterLocation)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP CONFIG
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'vitaldrop123@gmail.com';
        $mail->Password = 'sbdo lceo dnfq imbi';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // EMAIL SETTINGS
        $mail->setFrom('vitaldrop123@gmail.com', 'Vital Drop');
        $mail->addAddress($donorEmail);

        $mail->isHTML(true);
        $mail->Subject = "Urgent: Blood Donation Request ($bloodGroup)";

        // Safe names and details
        $safeDonorName = htmlspecialchars($donorName);
        $safeRequesterName = htmlspecialchars($requesterName);
        $safeBloodGroup = htmlspecialchars($bloodGroup);
        $safeRequesterEmail = htmlspecialchars($requesterEmail);
        $safeRequesterPhone = htmlspecialchars($requesterPhone);
        $safeRequesterLocation = htmlspecialchars($requesterLocation);

        // EMBED LOGO 
        $mail->addEmbeddedImage('../images/logo.png', 'logo_cid');

        // EMAIL BODY
        $mail->Body = "
<div style='font-family:Arial;padding:20px;max-width:600px;margin:auto'>
    <h2 style='text-align:center;'>Vital Drop</h2>
    <p>Dear <b>$safeDonorName</b>,</p>
    <p><strong>$safeRequesterName</strong> has requested your help as they are in need of your <strong>$safeBloodGroup</strong> blood type.</p>
    <p>Please check your Vital Drop dashboard to get in touch with them if you are available to donate.</p>
    <br>
    <p>Best Regards,<br>Vital Drop Team</p>
    
    <hr style='border: 1px solid #eee; margin-top: 20px; margin-bottom: 20px;'>
    <h3 style='color: #4d0000; margin-bottom: 10px;'>Receiver's Contact Details:</h3>
    <ul style='list-style-type: none; padding: 0;'>
        <li><strong>Name:</strong> $safeRequesterName</li>
        <li><strong>Location:</strong> $safeRequesterLocation</li>
        <li><strong>Email:</strong> <a href='mailto:$safeRequesterEmail' style='color: #a90000; text-decoration: none;'>$safeRequesterEmail</a></li>
        <li><strong>Phone Number:</strong> <a href='tel:$safeRequesterPhone' style='color: #a90000; text-decoration: none;'>$safeRequesterPhone</a></li>
    </ul>

    <img src='cid:logo_cid' style='width:120px;margin-top:10px'>
</div>
";

        // FALLBACK TEXT EMAIL
        $mail->AltBody =
            "Dear $safeDonorName,\n\n" .
            "$safeRequesterName has requested your help as they are in need of your $safeBloodGroup blood type.\n\n" .
            "Please check your Vital Drop dashboard to get in touch with them if you are available to donate.\n\n" .
            "Best Regards,\nVital Drop Team\n\n" .
            "---\n" .
            "Receiver's Contact Details:\n" .
            "Name: $safeRequesterName\n" .
            "Location: $safeRequesterLocation\n" .
            "Email: $safeRequesterEmail\n" .
            "Phone Number: $safeRequesterPhone";

        // SEND EMAIL
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}