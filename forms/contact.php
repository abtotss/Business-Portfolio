<?php
// contact.php

// Set the response header for JSON output
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Initialize an errors array
    $errors = [];

    // Retrieve and trim inputs
    $name    = trim($_POST["name"] ?? '');
    $email   = trim($_POST["email"] ?? '');
    $subject = trim($_POST["subject"] ?? '');
    $message = trim($_POST["message"] ?? '');

    // Sanitize string inputs using htmlspecialchars to avoid deprecated filters
    $name    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    // Sanitize the email address
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }
    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    if (empty($message)) {
        $errors[] = "Message is required.";
    }

    // Return errors as JSON if any exist
    if (!empty($errors)) {
        echo json_encode([
            "status"  => "error",
            "message" => implode(" ", $errors)
        ]);
        exit();
    }

    // Email configuration
    $to            = "info@rocketnet.digital"; // Replace with your actual email address
    $emailSubject  = "New Contact Message: " . $subject;
    $emailBody     = "Name: {$name}\n";
    $emailBody    .= "Email: {$email}\n\n";
    $emailBody    .= "Message:\n{$message}\n";
    $headers       = "From: {$email}\r\n";
    $headers      .= "Reply-To: {$email}\r\n";
    $headers      .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Attempt to send the email
    if (mail($to, $emailSubject, $emailBody, $headers)) {
        echo json_encode([
            "status"  => "success",
            "message" => "Your message has been sent. Thank you!"
        ]);
    } else {
        // Log error details for debugging
        error_log("Email sending failed. Data:\n" . print_r($_POST, true) . "\n", 3, "error_log.txt");
        echo json_encode([
            "status"  => "error",
            "message" => "Unable to send message. Please try again later."
        ]);
    }
} else {
    // For non-POST requests, return a forbidden access message
    http_response_code(403);
    echo json_encode([
        "status"  => "error",
        "message" => "Access forbidden."
    ]);
}
?>
