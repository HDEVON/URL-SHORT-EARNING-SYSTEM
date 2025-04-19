<div class="row">
    <div class="col-md-8 offset-md-2 text-center">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title">Earn Money with Short Links</h1>
                <p class="card-text">Create short links, share them with your friends, and earn money for every click!</p>
                
                <?php if (!isLoggedIn()): ?>
                    <div class="mt-4">
                        <a href="index.php?page=register" class="btn btn-primary btn-lg me-2">Sign Up Now</a>
                        <a href="index.php?page=login" class="btn btn-outline-primary btn-lg">Login</a>
                    </div>
                <?php else: ?>
                    <div class="mt-4">
                        <a href="index.php?page=create" class="btn btn-primary btn-lg">Create Short Link</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-link fa-3x text-primary mb-3"></i>
                <h3 class="card-title">Create Short Links</h3>
                <p class="card-text">Shorten your long URLs into easy-to-share links with just a few clicks.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-share-alt fa-3x text-primary mb-3"></i>
                <h3 class="card-title">Share Everywhere</h3>
                <p class="card-text">Share your links on social media, blogs, forums, or anywhere you want.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-dollar-sign fa-3x text-primary mb-3"></i>
                <h3 class="card-title">Earn Money</h3>
                <p class="card-text">Earn money for every person who clicks on your short links.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">How It Works</h3>
            </div>
            <div class="card-body">
                <ol class="list-group list-group-numbered">
                    <li class="list-group-item">Create an account on our platform.</li>
                    <li class="list-group-item">Shorten your long URLs using our tool.</li>
                    <li class="list-group-item">Share your short links with friends, on social media, or anywhere else.</li>
                    <li class="list-group-item">Earn money for every unique click on your links.</li>
                    <li class="list-group-item">Withdraw your earnings once you reach the minimum threshold.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Referral Program</h3>
            </div>
            <div class="card-body">
                <p>Invite your friends to join our platform and earn <?php echo REFERRAL_COMMISSION * 100; ?>% of their earnings as commission!</p>
                
                <?php if (isLoggedIn()): ?>
                    <?php $user = getCurrentUser(); ?>
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
                <?php else: ?>
                    <p>Sign up now to get your referral link and start earning more!</p>
                    <a href="index.php?page=register" class="btn btn-primary">Sign Up Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>