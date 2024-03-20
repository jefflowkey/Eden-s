<?php

require __DIR__.'/PHPMailer/src/Exception.php';
require __DIR__.'/PHPMailer/src/PHPMailer.php';
require __DIR__.'/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

if(isset($_POST["name"]) && isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['message'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $contact = new PHPMailer(true);

    // SMTP settings
    $contact->isSMTP();
    $contact->Host = 'mail.edensbloomsblossomsbouquets.co.ke';
    $contact->SMTPAuth = true;
    $contact->Username = 'info@edensbloomsblossomsbouquets.co.ke';
    $contact->Password = 'Ed3n5@2024';
    $contact->SMTPSecure = 'ssl';
    $contact->Port = 465;
    
    // Email settings
    $contact->isHTML(true);
    $contact->setFrom($email, $name);
    $contact->addAddress('info@edensbloomsblossomsbouquets.co.ke');
    $contact->Subject = $subject;

    // Email body
    $emailBody = "
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong></p>
        <p>$message</p>";

    $contact->Body = $emailBody;

    if($contact->send()) {
        exit("Message has been sent!");
    } else {
        exit("Failed to send message. Please try again later.");
    }
} else {
    exit("Incomplete data");
}
?>
