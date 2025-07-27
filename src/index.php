<?php
require_once 'functions.php';

$message = '';
$step = 'email'; // Default step

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['action']) && $_POST['action'] === 'send_code') {
        // Step 1: Send verification code
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            $code = generateVerificationCode();
            $_SESSION['verification_codes'][$email] = $code;
            $_SESSION['pending_email'] = $email;
            
            if (sendVerificationEmail($email, $code)) {
                $message = "Verification code sent to $email";
                $step = 'verify';
            } else {
                $message = "Failed to send verification email. Please try again.";
            }
        } else {
            $message = "Please enter a valid email address.";
        }
    } elseif (isset($_POST['verification_code']) && isset($_POST['action']) && $_POST['action'] === 'verify_code') {
        // Step 2: Verify code and register
        $code = $_POST['verification_code'];
        $email = $_SESSION['pending_email'] ?? '';
        
        if (verifyCode($email, $code)) {
            registerEmail($email);
            $message = "Email successfully registered! You will receive daily XKCD comics.";
            unset($_SESSION['verification_codes'][$email]);
            unset($_SESSION['pending_email']);
            $step = 'success';
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
    <title>XKCD Email Subscription</title>
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
            color: #333;
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
            border-color: #007cba;
            outline: none;
        }
        button {
            background-color: #007cba;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #005a87;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 XKCD Daily Comics Subscription</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'success') !== false || strpos($message, 'sent') !== false ? 'success' : (strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false ? 'error' : 'info'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Email Input Form (Always visible) -->
        <form method="POST" style="margin-bottom: 30px;">
            <div class="step-indicator">Step 1: Enter your email address</div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email" required placeholder="your.email@example.com">
            </div>
            <input type="hidden" name="action" value="send_code">
            <button type="submit" id="submit-email">Submit</button>
        </form>

        <!-- Verification Code Form (Always visible) -->
        <form method="POST">
            <div class="step-indicator">Step 2: Enter verification code</div>
            <div class="form-group">
                <label for="verification_code">Verification Code:</label>
                <input type="text" name="verification_code" id="verification_code" maxlength="6" required placeholder="123456">
            </div>
            <input type="hidden" name="action" value="verify_code">
            <button type="submit" id="submit-verification">Verify</button>
        </form>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p><strong>How it works:</strong></p>
            <p>1. Enter your email and click Submit<br>
            2. Check your email for a 6-digit verification code<br>
            3. Enter the code and click Verify<br>
            4. Enjoy daily XKCD comics in your inbox!</p>
            
            <p style="margin-top: 20px;">
                <a href="unsubscribe.php" style="color: #007cba; text-decoration: none;">
                    Want to unsubscribe? Click here
                </a>
            </p>
        </div>
    </div>
</body>
</html>