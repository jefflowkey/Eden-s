<?php
require __DIR__.'/PHPMailer/src/Exception.php';
require __DIR__.'/PHPMailer/src/PHPMailer.php';
require __DIR__.'/PHPMailer/src/SMTP.php';
require __DIR__.'/tcpdf/tcpdf.php';

use PHPMailer\PHPMailer\PHPMailer;

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    if(isset($_POST["firstName"], $_POST["lastName"], $_POST["emailX"], $_POST["deliveryLocation"], $_POST["flowers"], $_POST["phoneNumber"])) {
        // Get form data and sanitize inputs
        $firstName = htmlspecialchars($_POST["firstName"]);
        $lastName = htmlspecialchars($_POST["lastName"]);
        $email = filter_var($_POST["emailX"], FILTER_SANITIZE_EMAIL);
        $phoneNumber = filter_var($_POST["phoneNumber"], FILTER_SANITIZE_STRING);
        $deliveryLocation = htmlspecialchars($_POST["deliveryLocation"]);
        $flowers = implode(", ", $_POST["flowers"]);

        // Prepare PDF content
        $pdfContent = "
            <h3>Order Confirmation</h3>
            <table cellpadding='5' border='1'>
                <tr>
                    <td>First Name:</td>
                    <td>$firstName</td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td>$lastName</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>$email</td>
                </tr>
                <tr>
                    <td>Phone Number:</td>
                    <td>$phoneNumber</td>
                </tr>
                <tr>
                    <td>Delivery Location:</td>
                    <td>$deliveryLocation</td>
                </tr>
                <tr>
                    <td>Flowers:</td>
                    <td>$flowers</td>
                </tr>
            </table>";

        // Create TCPDF instance
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle("Eden's Blooms, Blossoms & Bouquets");
        $pdf->SetHeaderData('', 0, "Eden's Blooms, Blossoms & Bouquets", '');

        // Set default header data
        $pdf->setPrintHeader(true);
        $pdf->SetHeaderMargin(5);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetFont('dejavusans', '', 10);

        // Add a page
        $pdf->AddPage();

        // Write PDF content
        $pdf->writeHTML($pdfContent);

        // Get PDF content as string
        $pdfString = $pdf->Output('', 'S');

        // Initialize PHPMailer for customer
        $userMail = new PHPMailer(true);

        try {
            // Server settings for customer email
            $userMail->isSMTP();
            $userMail->Host = 'mail.edensbloomsblossomsbouquets.co.ke';
            $userMail->SMTPAuth = true;
            $userMail->Username = 'enquiries@edensbloomsblossomsbouquets.co.ke';
            $userMail->Password = 'Ed3n5@2024';
            $userMail->SMTPSecure = 'ssl';
            $userMail->Port = 465;

            // Sender and recipient for customer email
            $userMail->setFrom('enquiries@edensbloomsblossomsbouquets.co.ke', 'Eden\'s Blooms Blossoms Bouquets');
            $userMail->addAddress($email);
            $userMail->addStringAttachment($pdfString, 'order_confirmation.pdf');

            // Email subject and body for customer
            $userMail->isHTML(true);
            $userMail->Subject = 'Order Confirmation';
            $userMail->Body = "
                <p>Dear $firstName,</p>
                <p>Thank you for placing your order with us. Below are the details of your order:</p>
                $pdfContent
                <br>
                <p>Your order has been received and is currently being processed.</p>
                <p>Our Team will reach out shortly.</p>
                <br>
                <p>Regards,<br>Eden's Blooms Blossoms Bouquets Team</p>";

            // Send email to customer
            $userMail->send();

            // Prepare email content for company
            $companyMessage = "New Order Received:\n\n"
                            . "First Name: $firstName\n"
                            . "Last Name: $lastName\n"
                            . "Email: $email\n"
                            . "Phone Number: $phoneNumber\n"
                            . "Delivery Location: $deliveryLocation\n"
                            . "Flowers: $flowers";

            // Initialize PHPMailer for company
            $companyMail = new PHPMailer(true);
            $companyMail->isSMTP();
            $companyMail->Host = 'mail.edensbloomsblossomsbouquets.co.ke';
            $companyMail->SMTPAuth = true;
            $companyMail->Username = 'enquiries@edensbloomsblossomsbouquets.co.ke';
            $companyMail->Password = 'Ed3n5@2024';
            $companyMail->SMTPSecure = 'ssl';
            $companyMail->Port = 465;
            $companyMail->SMTPDebug = 0;
            $companyMail->setFrom($email, $firstName . ' ' . $lastName);
            $companyMail->addAddress('enquiries@edensbloomsblossomsbouquets.co.ke');
            $companyMail->Subject = 'New Order';
            $companyMail->Body = $companyMessage;

            // Send email to company
            if ($companyMail->send()) {
                // Notify user about successful order placement
                echo json_encode(array("success" => true, "message" => "Your order was placed successfully!"));
            } else {
                // Notify user if there is an error
                echo json_encode(array("success" => false, "message" => "Failed to send email to company: {$companyMail->ErrorInfo}"));
            }
        } catch (Exception $e) {
            // Notify user if there is an error
            echo json_encode(array("success" => false, "message" => "Failed to send email: {$userMail->ErrorInfo}"));
        }

    } else {
        echo json_encode(array("success" => false, "message" => "Incomplete form data"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Form submission error"));
}
?>
