<?php
// contact.php

// Set response header for JSON
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize variables
    $errors = [];
    $name = filter_var(trim($_POST["name"] ?? ""), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["email"] ?? ""), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST["subject"] ?? ""), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST["message"] ?? ""), FILTER_SANITIZE_STRING);

    // Validate each input
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

    // If validation errors exist, return them as JSON
    if ($errors) {
        echo json_encode(["status" => "error", "message" => implode(" ", $errors)]);
        exit();
    }

    // Set up email variables
    $to = "your-email@example.com"; // Replace with your actual email
    $email_subject = "New Contact Message: " . $subject;
    $email_body = "Name: $name\n";
    $email_body .= "Email: $email\n\n";
    $email_body .= "Message:\n$message\n";
    $headers = "From: $email\nReply-To: $email";

    // Attempt to send the email
    $mail_sent = mail($to, $email_subject, $email_body, $headers);

    if ($mail_sent) {
        echo json_encode(["status" => "success", "message" => "Your message has been sent. Thank you!"]);
    } else {
        // Log error to file for debugging
        error_log("Email sending failed. Data:\n" . print_r($_POST, true) . "\n", 3, "error_log.txt");
        echo json_encode(["status" => "error", "message" => "Unable to send message. Please try again later."]);
    }
} else {
    // Return error for incorrect HTTP methods
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access forbidden."]);
}
?>
