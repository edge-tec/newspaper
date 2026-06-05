<?php
// Base URL of the application
define('SITE_URL', 'http://extract.mailszo.com'); // Adjust this for production

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'newspaper');
define('DB_PASS', 'newspaper'); // Change as needed
define('DB_NAME', 'newspaper');
// Site Information
define('SITE_NAME', 'মডার্ন নিউজ');
define('SITE_DESC', 'সর্বশেষ খবরের জন্য আপনার বিশ্বস্ত মাধ্যম।');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
