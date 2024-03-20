<?php

require __DIR__.'/PHPMailer/src/Exception.php';
require __DIR__.'/PHPMailer/src/PHPMailer.php';
require __DIR__.'/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Connect to database (replace with your database credentials)
        $servername = "localhost";
        $username = "edensblooms_admin";
        $password = "$0bQH;#0Yx)I";
        $dbname = "edensblooms_subscribers_list";
        
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Prepare and execute SQL statement
        $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // Retrieve the last ID inserted
            $last_id = $conn->insert_id;
            
            // Subscription successful
            echo json_encode(array("success" => true, "message" => "Subscription successful! Stay tuned for updates."));
            
            // Send email to the subscribed user using PHPMailer
            $userMail = new PHPMailer(true);
            try {
                // Server settings
                $userMail->SMTPDebug = 0;
                $userMail->isSMTP();
                $userMail->Host = 'mail.edensbloomsblossomsbouquets.co.ke'; 
                $userMail->SMTPAuth = true; 
                $userMail->Username = 'info@edensbloomsblossomsbouquets.co.ke';
                $userMail->Password = 'Ed3n5@2024';
                $userMail->SMTPSecure = 'ssl';                                  
                $userMail->Port = 465;
                
                // Recipients
                $userMail->setFrom('info@edensbloomsblossomsbouquets.co.ke', 'Info - Eden\'s Blooms, Blossoms & Bouquets');
                $userMail->addAddress($email);
                $userMail->isHTML(true);
                $userMail->Subject = 'Subscription Confirmation';
                $userMail->Body    = "
                    <p>Hello,
                    <br>
                    <p>Thank you for subscribing to our newsletter!</p>
                    <p>We look forward to keeping you updated with our latest news and offers.</p>
                    <p>If you didn't submit your email address to join our subscriber list, just ignore this email.</p>
                    <p>Best regards,<br>Eden's Blooms Blossoms Bouquets Team</p>";
                
                $userMail->send();
            } catch (Exception $e) {
                echo json_encode(array("success" => false, "message" => "Message could not be sent to user. Mailer Error: {$userMail->ErrorInfo}"));
            }

            // Send email to the info team
            $infoMail = new PHPMailer(true);
            try {
                // Server settings
                $infoMail->SMTPDebug = 0;
                $infoMail->isSMTP();
                $infoMail->Host = 'mail.edensbloomsblossomsbouquets.co.ke'; 
                $infoMail->SMTPAuth = true; 
                $infoMail->Username = 'info@edensbloomsblossomsbouquets.co.ke';
                $infoMail->Password = 'Ed3n5@2024';
                $infoMail->SMTPSecure = 'ssl';                                  
                $infoMail->Port = 465;
                
                // Recipients
                $infoMail->setFrom('info@edensbloomsblossomsbouquets.co.ke', 'Info');
                $infoMail->addAddress('info@edensbloomsblossomsbouquets.co.ke');
                $infoMail->isHTML(true);
                $infoMail->Subject = 'New Subscriber';
                $infoMail->Body    = "
                        <p>Hello,</p>
                        <p>A new user has subscribed to the newsletter with the following email address:</p>
                        <p>Email: $email</p>
                        <p>Regards,<br>Info Team</p>";
                
                $infoMail->send();
            } catch (Exception $e) {
                echo json_encode(array("success" => false, "message" => "Message could not be sent to info team. Mailer Error: {$infoMail->ErrorInfo}"));
            }
            
        } else {
            // Check if the error is due to duplicate entry
            if ($conn->errno == 1062) { // MySQL error code for duplicate entry
                echo json_encode(array("success" => false, "message" => "Email already exists."));
            } else {
                echo json_encode(array("success" => false, "message" => "Oops! Something went wrong. Please try again later."));
            }
        }
        
        // Close connection
        $stmt->close();
        $conn->close();
    } else {
        // Invalid email address
        echo json_encode(array("success" => false, "message" => "Invalid email address!"));
    }
    exit; // Stop further execution
}
?>
