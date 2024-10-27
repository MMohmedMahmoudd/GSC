<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the recipient email
$to = "info@globalchem-eg.com";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input values
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);
    
    // Validate required fields
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        // Prepare the email content
        $emailContent = "Name: $name\n";
        $emailContent .= "Email: $email\n\n";
        $emailContent .= "Subject: $subject\n\n";
        $emailContent .= "Message:\n$message\n";

        // Email headers
        $headers = "From: $name <$email>";

        // Send email
        if (mail($to, $subject, $emailContent, $headers)) {
            // Success response
            echo json_encode(["status" => "success", "message" => "Form submitted successfully!"]);
        } else {
            // Failure response
            echo json_encode(["status" => "error", "message" => "Failed to send message. Please try again."]);
        }
    } else {
        // Missing fields response
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
    }
} else {
    // Invalid request method response
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
