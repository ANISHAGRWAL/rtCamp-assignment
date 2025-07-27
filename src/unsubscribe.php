<?php
require_once 'functions.php';

$message = '';
$step = 'email'; // Default step

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email']) && isset($_POST['action']) && $_POST['action'] === 'send_unsubscribe_code') {
        // Step 1: Send unsubscribe verification code
        $email = filter_var($_POST['unsubscribe_email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            // Check if email is registered
            $registeredEmails = getRegisteredEmails();
            if (in_array($email, $registeredEmails)) {
                $code = generateVerificationCode();
                $_SESSION['unsubscribe_codes'][$email] = $code;
                $_SESSION['pending_unsubscribe_email'] = $email;
                
                if (sendUnsubscribeVerificationEmail($email, $code)) {
                    $message = "Unsubscribe verification code sent to $email";
                    $step = 'verify';
                } else {
                    $message = "Failed to send unsubscribe verification email. Please try again.";
                }
            } else {
                $message = "Email address not found in our subscription list.";
            }
        } else {
            $message = "Please enter a valid email address.";
        }
    } elseif (isset($_POST['verification_code']) && isset($_POST['action']) && $_POST['action'] === 'verify_unsubscribe') {
        // Step 2: Verify code and unsubscribe
        $code = $_POST['verification_code'];
        $email = $_SESSION['pending_unsubscribe_email'] ?? '';
        
        if (verifyUnsubscribeCode($email, $code)) {
            if (unsubscribeEmail($email)) {
                $message = "Successfully unsubscribed $email from XKCD comics.";
                unset($_SESSION['unsubscribe_codes'][$email]);
                unset($_SESSION['pending_unsubscribe_email']);
                $step = 'success';
            } else {
                $message = "Failed to unsubscribe. Please try again.";
                $step = 'verify';
            }
        } else {
            $message = "Invalid verification code. Please try again.";
            $step = 'verify';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - XKCD Email Subscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d73527;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #d73527;
            outline: none;
        }
        button {
            background-color: #d73527;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #b82e1f;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .step-indicator {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .back-link a {
            color: #007cba;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚫 Unsubscribe from XKCD Comics</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Successfully') !== false || strpos($message, 'sent') !== false ? 'success' : (strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false || strpos($message, 'not found') !== false ? 'error' : 'info'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Unsubscribe Email Form (Always visible) -->
        <form method="POST" style="margin-bottom: 30px;">
            <div class="step-indicator">Step 1: Enter your email address to unsubscribe</div>
            <div class="form-group">
                <label for="unsubscribe_email">Email Address:</label>
                <input type="email" name="unsubscribe_email" id="unsubscribe_email" required placeholder="your.email@example.com">
            </div>
            <input type="hidden" name="action" value="send_unsubscribe_code">
            <button type="submit" id="submit-unsubscribe">Unsubscribe</button>
        </form>

        <!-- Verification Code Form (Always visible) -->
        <form method="POST">
            <div class="step-indicator">Step 2: Enter verification code to confirm unsubscription</div>
            <div class="form-group">
                <label for="verification_code">Verification Code:</label>
                <input type="text" name="verification_code" id="verification_code" maxlength="6" required placeholder="123456">
            </div>
            <input type="hidden" name="action" value="verify_unsubscribe">
            <button type="submit" id="submit-verification">Verify</button>
        </form>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p><strong>How unsubscribe works:</strong></p>
            <p>1. Enter your registered email address<br>
            2. Check your email for a 6-digit verification code<br>
            3. Enter the code to confirm unsubscription<br>
            4. You'll stop receiving XKCD comics</p>
        </div>

        <div class="back-link">
            <a href="index.php">← Back to subscription page</a>
        </div>
    </div>
</body>
</html>