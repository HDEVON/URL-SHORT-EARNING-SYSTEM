<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shortlink_db');

// Site configuration
define('SITE_NAME', 'ShortLink Pro');
define('SITE_URL', 'http://localhost/shortlink');
define('ADMIN_EMAIL', 'admin@shortlinkpro.com');

// Earnings configuration
define('RATE_PER_CLICK', 0.005); // $0.005 per click
define('REFERRAL_COMMISSION', 0.20); // 20% commission on referral earnings

// Ad configuration
define('SHOW_ADS_SECONDS', 5); // Number of seconds to show ads before redirect
define('MAX_DAILY_LINKS', 10); // Maximum links a free user can create per day
?>