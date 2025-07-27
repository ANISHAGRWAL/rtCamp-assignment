<?php
/**
 * CRON Job Script - Sends daily XKCD comics to subscribers
 * This script should be executed by CRON every 24 hours
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required functions
require_once __DIR__ . '/functions.php';

// Log the CRON job execution
$logFile = __DIR__ . '/cron.log';
$timestamp = date('Y-m-d H:i:s');

function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
}

logMessage("CRON job started", $logFile);

try {
    // Check if there are any registered emails
    $registeredEmails = getRegisteredEmails();
    
    if (empty($registeredEmails)) {
        logMessage("No registered emails found. Exiting.", $logFile);
        exit(0);
    }
    
    logMessage("Found " . count($registeredEmails) . " registered emails", $logFile);
    
    // Send XKCD updates to all subscribers
    $result = sendXKCDUpdatesToSubscribers();
    
    if ($result) {
        logMessage("Successfully sent XKCD comics to all subscribers", $logFile);
        echo "CRON job completed successfully. XKCD comics sent to " . count($registeredEmails) . " subscribers.\n";
    } else {
        logMessage("Failed to send XKCD comics to subscribers", $logFile);
        echo "CRON job failed to send XKCD comics.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    $errorMessage = "CRON job error: " . $e->getMessage();
    logMessage($errorMessage, $logFile);
    echo $errorMessage . "\n";
    exit(1);
}

logMessage("CRON job completed", $logFile);
?>