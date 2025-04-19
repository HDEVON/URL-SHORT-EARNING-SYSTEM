<?php
// Get current user
$user = getCurrentUser();

// Pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Get user's earnings with pagination
$earnings = getUserEarnings($user['id'], $perPage, $offset);

// Get total number of earnings for pagination
$db = getDbConnection();
$stmt = $db->prepare("SELECT COUNT(*) FROM earnings WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalEarnings = $stmt->fetchColumn();
$totalPages = ceil($totalEarnings / $perPage);

// Get earnings statistics
$stmt = $db->prepare("SELECT SUM(amount) FROM earnings WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalEarned = $stmt->fetchColumn() ?: 0;

$today = date('Y-m-d');
$stmt = $db->prepare("SELECT SUM(amount) FROM earnings WHERE user_id = ? AND DATE(created_at) = ?");
$stmt->execute([$user['id'], $today]);
$todayEarned = $stmt->fetchColumn() ?: 0;

$yesterday = date('Y-m-d', strtotime('-1 day'));
$stmt = $db->prepare("SELECT SUM(amount) FROM earnings WHERE user_id = ? AND DATE(created_at) = ?");
$stmt->execute([$user['id'], $yesterday]);
$yesterdayEarned = $stmt->fetchColumn() ?: 0;

$thisMonth = date('Y-m');
$stmt = $db->prepare("SELECT SUM(amount) FROM earnings WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$user['id'], $thisMonth]);
$thisMonthEarned = $stmt->fetchColumn() ?: 0;

// Handle withdrawal request
$withdrawalError = '';
$withdrawalSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $amount = (float)$_POST['amount'];
    $paymentMethod = $_POST['payment_method'] ?? '';
    $paymentDetails = $_POST['payment_details'] ?? '';
    
    if ($amount <= 0) {
        $withdrawalError = 'Please enter a valid amount.';
    } elseif ($amount < 5) {
        $withdrawalError = 'Minimum withdrawal amount is $5.00.';
    } elseif ($amount > $user['balance']) {
        $withdrawalError = 'Insufficient balance.';
    } elseif (empty($paymentMethod)) {
        $withdrawalError = 'Please select a payment method.';
    } elseif (empty($paymentDetails)) {
        $withdrawalError = 'Please enter your payment details.';
    } else {
        $result = requestWithdrawal($user['id'], $amount, $paymentMethod, $paymentDetails);
        
        if ($result['success']) {
            $withdrawalSuccess = $result['message'];
            // Refresh user data
            $user = getCurrentUser();
        } else {
            $withdrawalError = $result['message'];
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1>My Earnings</h1>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Available Balance</h5>
                <h2>$<?php echo number_format($user['balance'], 2); ?></h2>
                <button type="button" class="btn btn-light mt-2" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                    Withdraw
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Today's Earnings</h5>
                <h2>$<?php echo number_format($todayEarned, 2); ?></h2>
                <p class="mb-0">Yesterday: $<?php echo number_format($yesterdayEarned, 2); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">This Month</h5>
                <h2>$<?php echo number_format($thisMonthEarned, 2); ?></h2>
                <p class="mb-0"><?php echo date('F Y'); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Total Earned</h5>
                <h2>$<?php echo number_format($totalEarned, 2); ?></h2>
                <p class="mb-0">Lifetime earnings</p>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawal Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="withdrawModalLabel">Withdraw Funds</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($withdrawalError): ?>
                    <div class="alert alert-danger"><?php echo $withdrawalError; ?></div>
                <?php endif; ?>
                
                <?php if ($withdrawalSuccess): ?>
                    <div class="alert alert-success"><?php echo $withdrawalSuccess; ?></div>
                <?php else: ?>
                    <form method="post" action="index.php?page=earnings">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount to Withdraw</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="amount" name="amount" min="5" max="<?php echo $user['balance']; ?>" step="0.01" required>
                            </div>
                            <div class="form-text">Minimum withdrawal: $5.00. Available balance: $<?php echo number_format($user['balance'], 2); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select payment method</option>
                                <option value="paypal">PayPal</option>
                                <option value="bitcoin">Bitcoin</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_details" class="form-label">Payment Details</label>
                            <textarea class="form-control" id="payment_details" name="payment_details" rows="3" required placeholder="Enter your payment details (e.g., PayPal email, Bitcoin address, bank account details)"></textarea>
                        </div>
                        <button type="submit" name="withdraw" class="btn btn-primary">Request Withdrawal</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Earnings History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($earnings)): ?>
                    <p>You haven't earned any money yet. <a href="index.php?page=create">Create and share links</a> to start earning!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Link</th>
                                    <th>Amount</th>
                                    <th>Referral Commission</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($earnings as $earning): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y H:i', strtotime($earning['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>/index.php?page=redirect&code=<?php echo $earning['short_code']; ?>" target="_blank">
                                                <?php echo $earning['short_code']; ?>
                                            </a>
                                        </td>
                                        <td class="text-success">+$<?php echo number_format($earning['amount'], 4); ?></td>
                                        <td>
                                            <?php if ($earning['referral_amount'] > 0): ?>
                                                <span class="text-info">+$<?php echo number_format($earning['referral_amount'], 4); ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $earning['ip_address']; ?></td>
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
                                        <a class="page-link" href="index.php?page=earnings&p=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?page=earnings&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=earnings&p=<?php echo $page + 1; ?>">Next</a>
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