<?php
// Generate a random string for short codes and referral codes
function generateRandomString($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Generate a unique short code
function generateUniqueShortCode() {
    $db = getDbConnection();
    
    do {
        $code = generateRandomString();
        $stmt = $db->prepare("SELECT id FROM links WHERE short_code = ?");
        $stmt->execute([$code]);
    } while ($stmt->rowCount() > 0);
    
    return $code;
}

// Generate a unique referral code
function generateUniqueReferralCode() {
    $db = getDbConnection();
    
    do {
        $code = generateRandomString(8);
        $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$code]);
    } while ($stmt->rowCount() > 0);
    
    return $code;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Create a short link
function createShortLink($userId, $originalUrl, $title = '') {
    $db = getDbConnection();
    
    // Check if user has reached daily limit
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) FROM links WHERE user_id = ? AND DATE(created_at) = ?");
    $stmt->execute([$userId, $today]);
    
    if ($stmt->fetchColumn() >= MAX_DAILY_LINKS) {
        return ['success' => false, 'message' => 'You have reached your daily limit of ' . MAX_DAILY_LINKS . ' links.'];
    }
    
    // Create the short link
    $shortCode = generateUniqueShortCode();
    
    $stmt = $db->prepare("INSERT INTO links (user_id, original_url, short_code, title) VALUES (?, ?, ?, ?)");
    $success = $stmt->execute([$userId, $originalUrl, $shortCode, $title]);
    
    if ($success) {
        return [
            'success' => true,
            'short_code' => $shortCode,
            'short_url' => SITE_URL . '/index.php?page=redirect&code=' . $shortCode
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to create short link.'];
    }
}

// Record a click and earnings
function recordClick($linkId, $userId, $ipAddress) {
    $db = getDbConnection();
    
    // Check if this IP has already clicked this link today
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT id FROM earnings WHERE link_id = ? AND ip_address = ? AND DATE(created_at) = ?");
    $stmt->execute([$linkId, $ipAddress, $today]);
    
    if ($stmt->rowCount() > 0) {
        return false; // Already clicked today
    }
    
    // Update link clicks
    $stmt = $db->prepare("UPDATE links SET clicks = clicks + 1 WHERE id = ?");
    $stmt->execute([$linkId]);
    
    // Get referrer info
    $stmt = $db->prepare("SELECT referred_by FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $referralAmount = 0;
    $referralTo = null;
    
    // Calculate earnings
    $amount = RATE_PER_CLICK;
    
    // Calculate referral commission if applicable
    if ($user && $user['referred_by']) {
        $referralAmount = $amount * REFERRAL_COMMISSION;
        $referralTo = $user['referred_by'];
        
        // Add commission to referrer's balance
        $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$referralAmount, $referralTo]);
    }
    
    // Add earnings to user's balance
    $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$amount, $userId]);
    
    // Record the earning
    $stmt = $db->prepare("INSERT INTO earnings (user_id, link_id, amount, referral_amount, referral_to, ip_address) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $linkId, $amount, $referralAmount, $referralTo, $ipAddress]);
    
    return true;
}

// Get user's links
function getUserLinks($userId, $limit = 10, $offset = 0) {
    $db = getDbConnection();
    
    // Cast limit and offset to integers to avoid SQL syntax errors
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    // Use the limit and offset directly in the query instead of as parameters
    $stmt = $db->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user's earnings
function getUserEarnings($userId, $limit = 10, $offset = 0) {
    $db = getDbConnection();
    
    // Cast limit and offset to integers
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    // Use the limit and offset directly in the query
    $stmt = $db->prepare("SELECT e.*, l.short_code, l.original_url 
                          FROM earnings e 
                          JOIN links l ON e.link_id = l.id 
                          WHERE e.user_id = ? 
                          ORDER BY e.created_at DESC 
                          LIMIT $limit OFFSET $offset");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user's referrals
function getUserReferrals($userId) {
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT id, username, email, created_at FROM users WHERE referred_by = ?");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user's referral earnings
function getUserReferralEarnings($userId, $limit = 10, $offset = 0) {
    $db = getDbConnection();
    
    // Cast limit and offset to integers
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    // Use the limit and offset directly in the query
    $stmt = $db->prepare("SELECT e.*, u.username, l.short_code 
                          FROM earnings e 
                          JOIN users u ON e.user_id = u.id 
                          JOIN links l ON e.link_id = l.id 
                          WHERE e.referral_to = ? AND e.referral_amount > 0 
                          ORDER BY e.created_at DESC 
                          LIMIT $limit OFFSET $offset");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Request withdrawal
function requestWithdrawal($userId, $amount, $paymentMethod, $paymentDetails) {
    $db = getDbConnection();
    
    // Check if user has enough balance
    $stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user['balance'] < $amount) {
        return ['success' => false, 'message' => 'Insufficient balance.'];
    }
    
    // Create withdrawal request
    $stmt = $db->prepare("INSERT INTO withdrawals (user_id, amount, payment_method, payment_details) 
                          VALUES (?, ?, ?, ?)");
    $success = $stmt->execute([$userId, $amount, $paymentMethod, $paymentDetails]);
    
    if ($success) {
        // Deduct amount from user's balance
        $stmt = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        
        return ['success' => true, 'message' => 'Withdrawal request submitted successfully.'];
    } else {
        return ['success' => false, 'message' => 'Failed to submit withdrawal request.'];
    }
}

// Validate URL
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
?>