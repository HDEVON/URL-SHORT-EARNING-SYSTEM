<?php
// Get current user data
$user = getCurrentUser();

// Get user's stats
$db = getDbConnection();

// Total links
$stmt = $db->prepare("SELECT COUNT(*) FROM links WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalLinks = $stmt->fetchColumn();

// Total clicks
$stmt = $db->prepare("SELECT SUM(clicks) FROM links WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalClicks = $stmt->fetchColumn() ?: 0;

// Today's earnings
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT SUM(amount) FROM earnings WHERE user_id = ? AND DATE(created_at) = ?");
$stmt->execute([$user['id'], $today]);
$todayEarnings = $stmt->fetchColumn() ?: 0;

// Total earnings
$stmt = $db->prepare("SELECT SUM(amount) FROM earnings WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalEarnings = $stmt->fetchColumn() ?: 0;

// Total referrals
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
$stmt->execute([$user['id']]);
$totalReferrals = $stmt->fetchColumn();

// Recent links
$recentLinks = getUserLinks($user['id'], 5);
?>

<div class="row">
    <div class="col-md-12">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Balance</h5>
                <h2>$<?php echo number_format($user['balance'], 2); ?></h2>
                <p>Available for withdrawal</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Today's Earnings</h5>
                <h2>$<?php echo number_format($todayEarnings, 2); ?></h2>
                <p>From all your links</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Total Links</h5>
                <h2><?php echo $totalLinks; ?></h2>
                <p>Created by you</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Total Clicks</h5>
                <h2><?php echo $totalClicks; ?></h2>
                <p>On all your links</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Quick Link Creator</h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=create" method="post">
                    <div class="mb-3">
                        <label for="original_url" class="form-label">URL to Shorten</label>
                        <input type="url" class="form-control" id="original_url" name="original_url" required placeholder="https://example.com">
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title (Optional)</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="My Awesome Link">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Short Link</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Referral Program</h5>
            </div>
            <div class="card-body">
                <p>Invite friends and earn <?php echo REFERRAL_COMMISSION * 100; ?>% of their earnings!</p>
                <p>You have <strong><?php echo $totalReferrals; ?></strong> referrals so far.</p>
                
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="<?php echo SITE_URL; ?>/index.php?page=register&ref=<?php echo $user['referral_code']; ?>" id="referralLink" readonly>
                    <button class="btn btn-outline-primary" type="button" onclick="copyReferralLink()">Copy</button>
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
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Recent Links</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentLinks)): ?>
                    <p>You haven't created any links yet. <a href="index.php?page=create">Create your first link now</a>!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Short URL</th>
                                    <th>Original URL</th>
                                    <th>Clicks</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLinks as $link): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($link['title'] ?: 'Untitled'); ?></td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>/index.php?page=redirect&code=<?php echo $link['short_code']; ?>" target="_blank">
                                                <?php echo SITE_URL; ?>/index.php?page=redirect&code=<?php echo $link['short_code']; ?>
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('<?php echo SITE_URL; ?>/index.php?page=redirect&code=<?php echo $link['short_code']; ?>')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($link['original_url']); ?>" target="_blank">
                                                <?php echo htmlspecialchars(substr($link['original_url'], 0, 30) . (strlen($link['original_url']) > 30 ? '...' : '')); ?>
                                            </a>
                                        </td>
                                        <td><?php echo $link['clicks']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($link['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="index.php?page=links" class="btn btn-primary">View All Links</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    var tempInput = document.createElement("input");
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    alert("Link copied to clipboard!");
}
</script>