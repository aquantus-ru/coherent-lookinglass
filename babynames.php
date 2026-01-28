<?php
require_once 'includes/db.php';

$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageTitle = 'Unofficial Baby Names';

if ($id > 0) {
    // Detail View Logic
    $stmt = $pdo->prepare("SELECT * FROM names WHERE id = ?");
    $stmt->execute([$id]);
    $name = $stmt->fetch();

    if ($name) {
        $pageTitle = htmlspecialchars($name['name']) . ' - Definition';
        $page = 'detail';
    } else {
        $page = '404'; // Or just redirect to home
    }
} else {
    $page = 'home';
}

include 'includes/header.php';

if ($page === 'detail' && isset($name)) {
    // Inline Detail View for simplicity in this file structure, or include view
    include 'views/detail.php';
} elseif ($page === '404') {
    include 'views/404.php';
} else {
    include 'views/home.php';
}

include 'includes/footer.php';
?>
