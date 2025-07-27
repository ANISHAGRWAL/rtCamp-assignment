<?php
session_start();

function generateVerificationCode() {
    // Generate and return a 6-digit numeric code
    return sprintf('%06d', rand(100000, 999999));
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    // Save verified email to registered_emails.txt
    $emails = [];
    
    // Read existing emails if file exists
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (!empty(trim($content))) {
            $emails = explode("\n", trim($content));
        }
    }
    
    // Add email if not already exists
    if (!in_array($email, $emails)) {
        $emails[] = $email;
        file_put_contents($file, implode("\n", $emails) . "\n");
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    // Remove email from registered_emails.txt
    if (!file_exists($file)) {
        return false;
    }
    
    $content = file_get_contents($file);
    if (empty(trim($content))) {
        return false;
    }
    
    $emails = explode("\n", trim($content));
    $emails = array_filter($emails, function($e) use ($email) {
        return trim($e) !== $email;
    });
    
    file_put_contents($file, implode("\n", $emails) . (empty($emails) ? "" : "\n"));
    return true;
}

function sendVerificationEmail($email, $code) {
    // Send an email containing the verification code
    $subject = "Your Verification Code";
    $body = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@example.com" . "\r\n";
    
    return mail($email, $subject, $body, $headers);
}

function sendUnsubscribeVerificationEmail($email, $code) {
    // Send an email containing the unsubscribe verification code
    $subject = "Confirm Un-subscription";
    $body = "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@example.com" . "\r\n";
    
    return mail($email, $subject, $body, $headers);
}

function verifyCode($email, $code) {
    // Check if the provided code matches the sent one
    if (!isset($_SESSION['verification_codes'][$email])) {
        return false;
    }
    
    $storedCode = $_SESSION['verification_codes'][$email];
    return $storedCode === $code;
}

function verifyUnsubscribeCode($email, $code) {
    // Check if the provided unsubscribe code matches the sent one
    if (!isset($_SESSION['unsubscribe_codes'][$email])) {
        return false;
    }
    
    $storedCode = $_SESSION['unsubscribe_codes'][$email];
    return $storedCode === $code;
}

function fetchAndFormatXKCDData(): string
{
    // ---- CONFIG ----------------------------------------------------------
    // XKCD #2865 is the latest as of 2 Jul 2025 – adjust periodically.
    $LATEST_XKCD_ID = 2865;

    // For local XAMPP/WAMP you may leave SSL checks off.  Switch to TRUE
    // in production to avoid MITM risks.
    $DISABLE_SSL_VERIFY = true;
    // ---------------------------------------------------------------------

    try {
        // 1) Pick a random (valid) comic ID each call
        $id  = random_int(1, $LATEST_XKCD_ID);
        $url = "https://xkcd.com/{$id}/info.0.json";

        // 2) Fetch JSON with cURL if possible
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_USERAGENT      => 'XKCD Email Bot 2.0',
                CURLOPT_SSL_VERIFYPEER => !$DISABLE_SSL_VERIFY,
                CURLOPT_SSL_VERIFYHOST => $DISABLE_SSL_VERIFY ? 0 : 2,
            ]);

            $json      = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($json === false || $curlError !== '') {
                throw new Exception("cURL error: $curlError");
            }
            if ($httpCode !== 200) {
                throw new Exception("Unexpected HTTP code: $httpCode");
            }
        } else {
            // 3) Fallback: file_get_contents with stream context
            $ctx = stream_context_create([
                'http' => [
                    'timeout'      => 15,
                    'user_agent'   => 'XKCD Email Bot 2.0',
                    'ignore_errors'=> true,
                ],
                'ssl'  => [
                    'verify_peer'      => !$DISABLE_SSL_VERIFY,
                    'verify_peer_name' => !$DISABLE_SSL_VERIFY,
                ],
            ]);

            $json = @file_get_contents($url, false, $ctx);
            if ($json === false) {
                // Retry once via plain HTTP (rare corporate‑proxy issue)
                $json = @file_get_contents(str_replace('https://', 'http://', $url), false, $ctx);
                if ($json === false) {
                    throw new Exception('file_get_contents failed on both HTTPS and HTTP');
                }
            }
        }

        // 4) Decode & validate JSON
        $data = json_decode($json, true);
        if (!$data || empty($data['img'])) {
            throw new Exception('Malformed JSON or missing "img" field');
        }

        // 5) Build HTML
        $html  = "<h2>XKCD Comic #{$data['num']}</h2>\n";
        $html .= "<img src=\"{$data['img']}\" alt=\"" . htmlspecialchars($data['alt']) . "\">\n";
        $html .= "<p><a href=\"http://localhost/rtCamp-assignment/src/unsubscribe.php\" id=\"unsubscribe-button\">Unsubscribe</a></p>";

        return $html;
    }
    // 6) Any failure → log & show fallback comic
    catch (Throwable $e) {
        error_log('[XKCD fetch] ' . $e->getMessage());

        return '<h2>XKCD Comic</h2>
                <img src="https://imgs.xkcd.com/comics/compiling.png" alt="Fallback XKCD Comic">
                <p><a href="http://localhost/rtCamp-assignment/src/unsubscribe.php" id="unsubscribe-button">Unsubscribe</a></p>';
    }
}
function sendXKCDUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    // Send formatted XKCD data to all registered emails
    
    if (!file_exists($file)) {
        return false;
    }
    
    $content = file_get_contents($file);
    if (empty(trim($content))) {
        return false;
    }
    
    $emails = explode("\n", trim($content));
    $xkcdContent = fetchAndFormatXKCDData();
    
    if (!$xkcdContent) {
        return false;
    }
    
    $subject = "Your XKCD Comic";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@example.com" . "\r\n";
    
    $success = true;
    foreach ($emails as $email) {
        $email = trim($email);
        if (!empty($email)) {
            if (!mail($email, $subject, $xkcdContent, $headers)) {
                $success = false;
                error_log("Failed to send XKCD email to: $email");
            }
        }
    }
    
    return $success;
}

function getRegisteredEmails() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) {
        return [];
    }
    
    $content = file_get_contents($file);
    if (empty(trim($content))) {
        return [];
    }
    
    return array_filter(explode("\n", trim($content)), function($email) {
        return !empty(trim($email));
    });
}
?>