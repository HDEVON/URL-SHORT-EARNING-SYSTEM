<?php
$error = '';
$success = '';
$shortUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalUrl = $_POST['original_url'] ?? '';
    $title = $_POST['title'] ?? '';
    
    if (empty($originalUrl)) {
        $error = 'Please enter a URL to shorten.';
    } elseif (!isValidUrl($originalUrl)) {
        $error = 'Please enter a valid URL.';
    } else {
        $userId = $_SESSION['user_id'];
        $result = createShortLink($userId, $originalUrl, $title);
        
        if ($result['success']) {
            $success = 'Short link created successfully!';
            $shortUrl = $result['short_url'];
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Create Short Link</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <div class="mt-2">
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo $shortUrl; ?>" id="shortUrl" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyShortUrl()">Copy</button>
                            </div>
                        </div>
                    </div>
                    <script>
                        function copyShortUrl() {
                            var copyText = document.getElementById("shortUrl");
                            copyText.select();
                            copyText.setSelectionRange(0, 99999);
                            document.execCommand("copy");
                            alert("Short URL copied to clipboard!");
                        }
                    </script>
                <?php endif; ?>
                
                <form method="post" action="index.php?page=create">
                    <div class="mb-3">
                        <label for="original_url" class="form-label">URL to Shorten</label>
                        <input type="url" class="form-control" id="original_url" name="original_url" required placeholder="https://example.com">
                        <div class="form-text">Enter the long URL that you want to shorten.</div>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title (Optional)</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="My Awesome Link">
                        <div class="form-text">Add a title to help you identify this link later.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Short Link</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Tips for Maximizing Earnings</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-share-alt text-primary me-2"></i>
                        <strong>Share on Social Media:</strong> Post your links on Facebook, Twitter, Instagram, and other social platforms.
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-users text-primary me-2"></i>
                        <strong>Invite Friends:</strong> Use your referral link to invite friends and earn commission on their clicks.
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-blog text-primary me-2"></i>
                        <strong>Blog Posts:</strong> Include your short links in blog posts or articles you write.
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-comment text-primary me-2"></i>
                        <strong>Forums & Comments:</strong> Share your links in relevant forum discussions or comment sections.
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <strong>Email Marketing:</strong> Include your short links in email newsletters.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>