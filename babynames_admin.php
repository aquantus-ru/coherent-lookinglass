<?php
session_start();
require_once 'includes/db.php';

$ADMIN_PASSWORD = 'admin'; // Simple hardcoded password

// Handle Login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
    } else {
        $error = "Invalid password.";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: babynames_admin.php");
    exit;
}

$pageTitle = 'Admin';
// We need to set a flag so header knows where to link
$isAdminPage = true;

include 'includes/header.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true):
?>
    <div class="box">
        <h3>Admin Login</h3>
        <div class="box-content">
            <form method="POST" class="form-container">
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-input" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Login</button>
                </div>
                <?php if (isset($error)): ?>
                    <p style="color:red;"><?php echo $error; ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
<?php else: ?>
    <?php include 'views/admin.php'; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
