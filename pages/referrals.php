<?php
// Get current user
$user = getCurrentUser();

// Pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get user's referrals with pagination
$db = getDbConnection();
// Cast limit and offset to integers
$perPage = (int)$perPage;
$offset = (int)$offset;

// Use the limit and offset directly in the query instead of as parameters
$stmt = $db->prepare("
    SELECT u.*, 
           (SELECT SUM(amount) FROM earnings WHERE user_id = u.id) as total_earnings,
           (SELECT COUNT(*) FROM links WHERE user_id = u.id) as total_links
    FROM users u 
    WHERE u.referred_by = ? 
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute([$user['id']]);
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total number of referrals for pagination
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
$stmt->execute([$user['id']]);
$totalReferrals = $stmt->fetchColumn();
$totalPages = ceil($totalReferrals / $perPage);

// Get referral statistics
$stmt = $db->prepare("
    SELECT SUM(e.referral_amount) as total_commission
    FROM earnings e
    WHERE e.referral_to = ?
");
$stmt->execute([$user['id']]);
$totalCommission = $stmt->fetchColumn() ?: 0;

// Get today's commission
$today = date('Y-m-d');
$stmt = $db->prepare("
    SELECT SUM(e.referral_amount) as today_commission
    FROM earnings e
    WHERE e.referral_to = ? AND DATE(e.created_at) = ?
");
$stmt->execute([$user['id'], $today]);
$todayCommission = $stmt->fetchColumn() ?: 0;

// Get this month's commission
$thisMonth = date('Y-m');
$stmt = $db->prepare("
    SELECT SUM(e.referral_amount) as month_commission
    FROM earnings e
    WHERE e.referral_to = ? AND DATE_FORMAT(e.created_at, '%Y-%m') = ?
");
$stmt->execute([$user['id'], $thisMonth]);
$monthCommission = $stmt->fetchColumn() ?: 0;
?>

<div class="row">
    <div class="col-md-12">
        <h1>My Referrals</h1>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Referrals</h5>
                <h2><?php echo $totalReferrals; ?></h2>
                <p class="mb-0">Users you've referred</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Today's Commission</h5>
                <h2>$<?php echo number_format($todayCommission, 2); ?></h2>
                <p class="mb-0">From referral earnings</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">This Month</h5>
                <h2>$<?php echo number_format($monthCommission, 2); ?></h2>
                <p class="mb-0"><?php echo date('F Y'); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Total Commission</h5>
                <h2>$<?php echo number_format($totalCommission, 2); ?></h2>
                <p class="mb-0">Lifetime referral earnings</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Your Referral Link</h5>
            </div>
            <div class="card-body">
                <p>Share this link with your friends, family, or on social media to earn <?php echo REFERRAL_COMMISSION * 100; ?>% commission on their earnings!</p>
                
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="<?php echo SITE_URL; ?>/index.php?page=register&ref=<?php echo $user['referral_code']; ?>" id="referralLink" readonly>
                    <button class="btn btn-outline-primary" type="button" onclick="copyReferralLink()">Copy</button>
                </div>
                
                <div class="mt-3">
                    <h6>Share on Social Media:</h6>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/index.php?page=register&ref=' . $user['referral_code']); ?>" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/index.php?page=register&ref=' . $user['referral_code']); ?>&text=<?php echo urlencode('Join me on ' . SITE_NAME . ' and earn money by shortening links!'); ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode('Join me on ' . SITE_NAME . ' and earn money by shortening links! ' . SITE_URL . '/index.php?page=register&ref=' . $user['referral_code']); ?>" target="_blank" class="btn btn-sm btn-success">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
                
                <script>
                    function copyReferralLink() {
                        var copyText = document.getElementById("referralLink");
                        copyText.select();
                        copyText.setSelectionRange(0, 99999);
                        document.execCommand("copy");
                        alert("Referral link copied to clipboard!");
                    }
                </script>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Referral Program</h5>
            </div>
            <div class="card-body">
                <h5>How it works:</h5>
                <ol>
                    <li>Share your referral link with friends</li>
                    <li>When they sign up using your link, they become your referral</li>
                    <li>You earn <?php echo REFERRAL_COMMISSION * 100; ?>% commission on all their earnings</li>
                    <li>Your commissions are added to your balance automatically</li>
                </ol>
                
                <div class="alert alert-info">
                    <strong>Tip:</strong> The more active referrals you have, the more passive income you'll earn!
                </div>
                
                <h5>Best places to promote your referral link:</h5>
                <ul>
                    <li>Social media profiles and posts</li>
                    <li>Email newsletters</li>
                    <li>Forums and online communities</li>
                    <li>Your website or blog</li>
                    <li>YouTube videos or descriptions</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Your Referrals</h5>
            </div>
            <div class="card-body">
                <?php if (empty($referrals)): ?>
                    <p>You haven't referred anyone yet. Share your referral link to start earning commission!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Joined</th>
                                    <th>Total Links</th>
                                    <th>Total Earnings</th>
                                    <th>Your Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($referrals as $referral): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($referral['username']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($referral['created_at'])); ?></td>
                                        <td><?php echo $referral['total_links'] ?: 0; ?></td>
                                        <td>$<?php echo number_format($referral['total_earnings'] ?: 0, 2); ?></td>
                                        <td class="text-success">$<?php echo number_format(($referral['total_earnings'] ?: 0) * REFERRAL_COMMISSION, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=referrals&p=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?page=referrals&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=referrals&p=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>