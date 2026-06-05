<?php
// Base URL of the application
define('SITE_URL', 'http://localhost/Newspaper website'); // Adjust this for production

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change as needed
define('DB_NAME', 'newspaper_db');

// Site Information
define('SITE_NAME', 'Modern News');
define('SITE_DESC', 'Your trusted source for the latest news.');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
