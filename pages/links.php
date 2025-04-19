<?php
// Get current user
$user = getCurrentUser();

// Pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get user's links with pagination
$links = getUserLinks($user['id'], $perPage, $offset);

// Get total number of links for pagination
$db = getDbConnection();
$stmt = $db->prepare("SELECT COUNT(*) FROM links WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalLinks = $stmt->fetchColumn();
$totalPages = ceil($totalLinks / $perPage);

// Handle link deletion
if (isset($_POST['delete_link']) && isset($_POST['link_id'])) {
    $linkId = (int)$_POST['link_id'];
    
    // Verify the link belongs to the user
    $stmt = $db->prepare("SELECT id FROM links WHERE id = ? AND user_id = ?");
    $stmt->execute([$linkId, $user['id']]);
    
    if ($stmt->rowCount() > 0) {
        $stmt = $db->prepare("DELETE FROM links WHERE id = ?");
        $success = $stmt->execute([$linkId]);
        
        if ($success) {
            // Redirect to refresh the page
            header('Location: index.php?page=links&deleted=1');
            exit;
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1>My Links</h1>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">Link deleted successfully.</div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Your Short Links</h5>
                <a href="index.php?page=create" class="btn btn-light btn-sm">Create New Link</a>
            </div>
            <div class="card-body">
                <?php if (empty($links)): ?>
                    <p>You haven't created any links yet. <a href="index.php?page=create">Create your first link now</a>!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Short URL</th>
                                    <th>Original URL</th>
                                    <th>Clicks</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($links as $link): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($link['title'] ?: 'Untitled'); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="<?php echo SITE_URL; ?>/index.php?page=redirect&code=<?php echo $link['short_code']; ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                                    <?php echo SITE_URL; ?>/index.php?page=redirect&code=<?php echo $link['short_code']; ?>
                                                </a>
                                                <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('<?php echo SITE_URL; ?>/index.php?page=redirect&code=<?php echo $link['short_code']; ?>')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($link['original_url']); ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                                <?php echo htmlspecialchars($link['original_url']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo $link['clicks']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($link['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo SITE_URL; ?>/index.php?page=redirect&code=<?php echo $link['short_code']; ?>" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="fas fa-external-link-alt"></i> Visit
                                                </a>
                                                <form method="post" action="index.php?page=links" onsubmit="return confirm('Are you sure you want to delete this link?');" class="d-inline">
                                                    <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                                                    <button type="submit" name="delete_link" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
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
                                        <a class="page-link" href="index.php?page=links&p=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?page=links&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=links&p=<?php echo $page + 1; ?>">Next</a>
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