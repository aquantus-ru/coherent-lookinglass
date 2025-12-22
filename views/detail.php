<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM names WHERE id = ?");
$stmt->execute([$id]);
$name = $stmt->fetch();

if (!$name) {
    echo "<h2>Name not found</h2>";
    return;
}
$currentUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<div class="box">
    <h3>Name Definition</h3>
    <div class="box-content">
        <h2 style="margin-top: 0;"><?php echo htmlspecialchars($name['name']); ?></h2>
        <blockquote class="article-blockquote">
            <?php echo htmlspecialchars($name['definition']); ?>
        </blockquote>
        <p class="small-text">Added on <?php echo htmlspecialchars($name['created_at']); ?></p>

        <hr>

        <h4>Share this name</h4>
        <div style="display: flex; gap: 10px;">
            <a href="https://twitter.com/intent/tweet?text=Check out the unofficial definition of <?php echo urlencode($name['name']); ?>: <?php echo urlencode($currentUrl); ?>" target="_blank" class="btn">Share on Twitter</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($currentUrl); ?>" target="_blank" class="btn">Share on Facebook</a>
            <button onclick="navigator.clipboard.writeText('<?php echo $currentUrl; ?>'); alert('Link copied!');" class="btn">Copy Link</button>
        </div>
    </div>
</div>
