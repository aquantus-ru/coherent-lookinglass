<?php
session_start();
define('DB_FILE', __DIR__ . '/baby_names.db');
$ADMIN_PASSWORD = 'admin';

function getDB() {
    $dsn = 'sqlite:' . DB_FILE;
    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

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

// API Handling (Admin Actions)
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $pdo = getDB();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE names SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE names SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

$pageTitle = 'Admin';
$pdo = getDB();
$pending = $pdo->query("SELECT * FROM names WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
$all = $pdo->query("SELECT * FROM names ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $pageTitle; ?> | COHERENT</title>
<style>
/* --- Reuse Same CSS for Consistency --- */
html, body { margin: 0; padding: 0; height: 100%; }
body { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; color: #333; margin: 0; background-color: #003366; background-image: linear-gradient(to bottom, #003366 0%, #004b8d 100%); background-attachment: fixed; }
#page-wrapper { width: 960px; margin: 0 auto; background-color: #fff; border-left: 1px solid #5d7b9d; border-right: 1px solid #5d7b9d; min-height: 100vh; box-shadow: 0 0 10px rgba(0,0,0,0.3); display: flex; flex-direction: column; }
a { color: #003399; text-decoration: none; } a:hover { text-decoration: underline; } .bold { font-weight: bold; }
.super-topbar { display: flex; justify-content: space-between; padding: 3px 10px; background-color: #dbe8f9; color: #666; font-size: 10px; border-bottom: 1px solid #bad2ea; }
.super-topbar .right-links a { color: #444; margin-left: 10px; }
.header { background: #fff; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
.header .logo h1 { font-family: 'Arial Black', Arial, sans-serif; font-size: 24px; color: #003366; margin: 0; letter-spacing: -1px; }
.channel-nav { background-color: #0d4a97; border-top: 1px solid #5a8bc3; border-bottom: 3px solid #fff; padding: 0 10px; height: 22px; line-height: 22px; }
.channel-nav ul { list-style: none; margin: 0; padding: 0; display: flex; } .channel-nav a { display: block; color: #fff; padding: 0 10px; font-weight: bold; font-size: 11px; text-decoration: none; border-right: 1px solid #4a7bb5; } .channel-nav a:hover { background-color: #3673bd; }
.container { display: grid; grid-template-columns: 200px 1fr; gap: 15px; padding: 15px; align-items: start; flex: 1; }
.sidebar-col { display: flex; flex-direction: column; gap: 15px; }
.box { border: 1px solid #9fbdda; background: #fff; }
.box h3 { background: linear-gradient(to bottom, #ffffff 0%, #dcebfb 50%, #cce1f8 100%); border-bottom: 1px solid #9fbdda; padding: 5px 8px; margin: 0; font-size: 11px; color: #003366; font-weight: bold; text-transform: uppercase; }
.box-content { padding: 8px; font-size: 11px; }
.sidebar-nav-list { list-style: none; padding: 0; margin: 0; } .sidebar-nav-list li { border-bottom: 1px dotted #ccc; padding: 4px 0; } .sidebar-nav-list a { display: block; color: #003399; }
footer { margin-top: 20px; border-top: 3px solid #003366; background: #f7f7f7; padding: 10px 15px; font-size: 10px; color: #666; display: flex; justify-content: space-between; align-items: center; }
/* Form/Table */
input[type="text"], input[type="password"] { border: 1px solid #7f9db9; font-size: 11px; padding: 3px; }
.btn-primary { background-color: #2a81cf; color: white; border: 1px solid #145b9a; font-weight: bold; padding: 2px 8px; cursor: pointer; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 11px; border: 1px solid #ccc; }
table th { background: #e6eff9; color: #003366; font-weight: bold; text-align: left; padding: 5px 8px; border-bottom: 1px solid #9fbdda; }
table td { padding: 5px 8px; border-bottom: 1px solid #eee; }
table tr:nth-child(even) { background-color: #faffff; }
.form-group { margin-bottom: 10px; } .form-label { display: block; font-weight: bold; margin-bottom: 3px; }
</style>
</head>
<body>

<div id="page-wrapper">
  <div class="super-topbar">
    <div class="left-links"><span class="bold"><?php echo date('l, F d, Y'); ?></span></div>
    <div class="right-links">
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a href="babynames_admin.php?logout=1">LOGOUT</a>
        <?php else: ?>
            <a href="babynames_admin.php">ADMIN</a>
        <?php endif; ?>
    </div>
  </div>

  <div class="header">
    <div class="logo">
        <h1><a href="babynames.php" style="color: #003366; text-decoration: none;">BABY NAMES<span style="font-weight:normal; font-family:Arial; font-size:20px; color:#a3c4e6;">.unofficial</span></a></h1>
    </div>
  </div>

  <div class="channel-nav">
    <ul>
        <li><a href="babynames.php">Home</a></li>
        <li><a href="babynames.php">All Names</a></li>
    </ul>
  </div>

  <div class="container">
    <div class="sidebar-col">
      <div class="box">
        <h3>Navigation</h3>
        <div class="box-content">
          <ul class="sidebar-nav-list">
            <li><a href="babynames.php">Home</a></li>
            <li><a href="babynames_admin.php"><b>Admin Dashboard</b></a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="main-col">
        <?php if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true): ?>
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
            <div class="box">
                <h3>Admin Dashboard</h3>
                <div class="box-content">
                    <h4>Pending Submissions</h4>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Definition</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pending) > 0): ?>
                                <?php foreach ($pending as $name): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($name['name']); ?></td>
                                    <td><?php echo htmlspecialchars($name['definition']); ?></td>
                                    <td><?php echo htmlspecialchars($name['created_at']); ?></td>
                                    <td>
                                        <button onclick="approveName(<?php echo $name['id']; ?>)" class="btn-primary">Approve</button>
                                        <button onclick="rejectName(<?php echo $name['id']; ?>)">Reject</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No pending submissions.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <h4 style="margin-top: 20px;">All Names</h4>
                    <table class="data-table">
                        <thead>
                            <tr><th>Name</th><th>Definition</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all as $name): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($name['name']); ?></td>
                                <td><?php echo htmlspecialchars($name['definition']); ?></td>
                                <td><?php echo htmlspecialchars($name['status']); ?></td>
                                <td>
                                    <button onclick="deleteName(<?php echo $name['id']; ?>)" style="color:red;">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
            function approveName(id) {
                fetch('babynames_admin.php?action=approve&id=' + id, { method: 'POST' })
                    .then(response => response.json())
                    .then(data => { if (data.success) location.reload(); });
            }

            function rejectName(id) {
                fetch('babynames_admin.php?action=reject&id=' + id, { method: 'POST' })
                    .then(response => response.json())
                    .then(data => { if (data.success) location.reload(); });
            }

            function deleteName(id) {
                if(confirm('Are you sure?')) {
                    rejectName(id); // Use reject logic for delete
                }
            }
            </script>
        <?php endif; ?>
    </div>
  </div>

  <footer>
    <div class="left-links"><a href="#">Privacy</a></div>
    <div class="copyright">&copy; <?php echo date('Y'); ?> Unofficial Baby Names</div>
  </footer>
</div>

</body>
</html>
