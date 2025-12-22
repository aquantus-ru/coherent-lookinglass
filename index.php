<?php
require_once 'includes/db.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$pageTitle = 'Unofficial Baby Names';

// Basic routing
switch ($page) {
    case 'admin':
        $pageTitle = 'Admin';
        break;
    case 'detail':
        $pageTitle = 'Name Detail';
        break;
    default:
        $pageTitle = 'Unofficial Baby Names';
        break;
}

include 'includes/header.php';

switch ($page) {
    case 'admin':
        include 'views/admin.php';
        break;
    case 'detail':
        include 'views/detail.php';
        break;
    default:
        include 'views/home.php';
        break;
}

include 'includes/footer.php';
?>
