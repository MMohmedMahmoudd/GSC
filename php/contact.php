<?php
// Enable error reporting (remove these lines in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SMTP Configuration
$smtpHost = 'mail.globalchem-eg.com';
$smtpPort = 465;
$smtpUser = 'siterequests@globalchem-eg.com';
$smtpPass = 'aI){)9,CI~tf'; // Replace with your actual SMTP password

// Override PHP mail configuration temporarily
ini_set('SMTP', $smtpHost);
ini_set('smtp_port', $smtpPort);
ini_set('sendmail_from', $smtpUser);

// Email details
$to = 'info@globalchem-eg.com';
$subject = 'Form Submission from GlobalChem Website';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate inputs
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
        exit;
    }

    // Validate phone number format
    if (!preg_match('/^(?:01[0125]\d{8}|\+?[1-9]\d{1,14})$/', $phone)) {
        echo json_encode(["status" => "error", "message" => "Please enter a valid phone number."]);
        exit;
    }

    // Verify reCAPTCHA
    if (empty($recaptchaResponse)) {
        echo json_encode(["status" => "error", "message" => "reCAPTCHA verification failed."]);
        exit;
    }

    $recaptchaSecret = '6LctNoMqAAAAAGu7SWH-6bobsbpMo0Lx6XYU5Mmp'; // Replace with your reCAPTCHA v3 Secret Key
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';

    $recaptchaData = [
        'secret' => $recaptchaSecret,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'],
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($recaptchaData),
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($recaptchaUrl, false, $context);

    if ($result === false) {
        echo json_encode(["status" => "error", "message" => "Failed to validate reCAPTCHA."]);
        exit;
    }

    $recaptchaResult = json_decode($result, true);

    if ($recaptchaResult['success'] && $recaptchaResult['score'] >= 0.5) {
        // Prepare email content
        $emailContent = "Name: $name\n";
        $emailContent .= "Email: $email\n";
        $emailContent .= "Phone: $phone\n\n";
        $emailContent .= "Message:\n$message";

        $headers = "From: $name <$email>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Send email
        if (mail($to, $subject, $emailContent, $headers)) {
            echo json_encode(["status" => "success", "message" => "Form submitted successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "There was an error sending your message. Please try again later."]);
        }
    } else {
        // reCAPTCHA verification failed
        echo json_encode(["status" => "error", "message" => "reCAPTCHA verification failed."]);
    }
}
?>
