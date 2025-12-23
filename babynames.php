<?php
// DB Connection Logic
define('DB_FILE', __DIR__ . '/baby_names.db');
function getDB() {
    $dsn = 'sqlite:' . DB_FILE;
    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Create table if not exists (in case it was deleted)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS names (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                definition TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// API Handling
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action) {
    header('Content-Type: application/json');
    $pdo = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['name']) && isset($input['definition'])) {
            $stmt = $pdo->prepare("INSERT INTO names (name, definition, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$input['name'], $input['definition']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
        }
        exit;
    }

    if ($action === 'search') {
        $query = isset($_GET['q']) ? $_GET['q'] : '';
        $stmt = $pdo->prepare("SELECT * FROM names WHERE status = 'approved' AND (name LIKE ? OR definition LIKE ?)");
        $term = '%' . $query . '%';
        $stmt->execute([$term, $term]);
        echo json_encode($stmt->fetchAll());
        exit;
    } elseif ($action === 'suggestions') {
        $stmt = $pdo->query("SELECT * FROM names WHERE status = 'approved' ORDER BY RANDOM() LIMIT 5");
        echo json_encode($stmt->fetchAll());
        exit;
    } elseif ($action === 'list') {
        $stmt = $pdo->query("SELECT * FROM names WHERE status = 'approved' ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
        exit;
    }
}

// View Logic
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageTitle = 'Unofficial Baby Names';
$name = null;

if ($id > 0) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM names WHERE id = ?");
    $stmt->execute([$id]);
    $name = $stmt->fetch();
    if ($name) {
        $pageTitle = htmlspecialchars($name['name']) . ' - Definition';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $pageTitle; ?> | COHERENT</title>
<style>
/* --- MSN 2007 Style Sheet (Refined) --- */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
}
body {
  font-family: Verdana, Arial, Helvetica, sans-serif;
  font-size: 11px;
  color: #333;
  margin: 0;
  background-color: #003366; /* Classic MSN Dark Blue */
  background-image: linear-gradient(to bottom, #003366 0%, #004b8d 100%);
  background-attachment: fixed;
}

/* --- Page Wrapper --- */
#page-wrapper {
  width: 960px;
  margin: 0 auto;
  background-color: #fff;
  border-left: 1px solid #5d7b9d;
  border-right: 1px solid #5d7b9d;
  min-height: 100vh;
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
}

/* --- Typography Utilities --- */
a { color: #003399; text-decoration: none; }
a:hover { text-decoration: underline; }
.small-text { font-size: 10px; color: #666; }
.bold { font-weight: bold; }

/* --- Top Utility Bar (Super Topbar) --- */
.super-topbar {
  display: flex;
  justify-content: space-between;
  padding: 3px 10px;
  background-color: #dbe8f9;
  color: #666;
  font-size: 10px;
  border-bottom: 1px solid #bad2ea;
}
.super-topbar .right-links a {
  color: #444;
  margin-left: 10px;
}

/* --- Main Brand Header --- */
.header {
  background: #fff;
  padding: 10px 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #ccc;
}
.header .logo h1 {
  font-family: 'Arial Black', Arial, sans-serif;
  font-size: 24px;
  color: #003366;
  margin: 0;
  letter-spacing: -1px;
}
.header .search-area {
  background: #eaf3ff;
  border: 1px solid #b3c9e5;
  padding: 4px;
  border-radius: 2px;
}
.header input[type="text"] {
  border: 1px solid #7f9db9;
  font-size: 11px;
  padding: 2px;
  width: 200px;
}
.header input[type="submit"] {
  background: url('https://placehold.co/1x20/2a81cf/2a81cf') repeat-x;
  background-color: #2a81cf;
  color: white;
  border: 1px solid #003366;
  font-size: 11px;
  font-weight: bold;
  cursor: pointer;
  padding: 1px 6px;
}

/* --- Channel Navigation (Tabs) --- */
.channel-nav {
  background-color: #0d4a97;
  border-top: 1px solid #5a8bc3;
  border-bottom: 3px solid #fff;
  padding: 0 10px;
  height: 22px;
  line-height: 22px;
}
.channel-nav ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
}
.channel-nav li { margin: 0; }
.channel-nav a {
  display: block;
  color: #fff;
  padding: 0 10px;
  font-weight: bold;
  font-size: 11px;
  text-decoration: none;
  border-right: 1px solid #4a7bb5;
}
.channel-nav a:hover { background-color: #3673bd; }

/* --- Sub-Channel Bar (Gray) --- */
.sub-channel-nav {
  background-color: #e3e3e3;
  border-bottom: 1px solid #ccc;
  padding: 4px 15px;
  font-size: 10px;
  color: #333;
}
.sub-channel-nav a { color: #333; margin-right: 10px; }

/* --- Welcome / Highlights Section --- */
.welcome-nav {
  background: #f1f7fe;
  border-bottom: 1px solid #d4e3f6;
  padding: 10px 15px;
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 20px;
}
.welcome-left h4 { margin: 0 0 5px; color: #c00; font-size: 11px; }

/* --- Main Layout --- */
.container {
  display: grid;
  grid-template-columns: 200px 1fr; /* Sidebar | Main */
  gap: 15px;
  padding: 15px;
  align-items: start;
  flex: 1; /* Pushes footer to bottom */
}

/* --- Sidebar Styling --- */
.sidebar-col { display: flex; flex-direction: column; gap: 15px; }

.box {
  border: 1px solid #9fbdda;
  background: #fff;
}
.box h3 {
  /* The Classic Gradient Header */
  background: linear-gradient(to bottom, #ffffff 0%, #dcebfb 50%, #cce1f8 100%);
  border-bottom: 1px solid #9fbdda;
  padding: 5px 8px;
  margin: 0;
  font-size: 11px;
  color: #003366;
  font-weight: bold;
  text-transform: uppercase;
}
.box-content { padding: 8px; font-size: 11px; }

/* Sidebar Nav Links */
.sidebar-nav-list { list-style: none; padding: 0; margin: 0; }
.sidebar-nav-list li {
  border-bottom: 1px dotted #ccc;
  padding: 4px 0;
}
.sidebar-nav-list li:last-child { border-bottom: none; }
.sidebar-nav-list a { display: block; color: #003399; }
.sidebar-nav-list a.current { font-weight: bold; color: #000; background-color: #f0f5fa; margin: -4px 0; padding: 4px 0; }
.sidebar-nav-list a:hover { background-color: #f7f7f7; text-decoration: none; color: #c00; }
.sidebar-nav-list .sub-link { padding-left: 12px; font-size: 10px; }

/* --- Main Content Styling --- */
.main-col { min-width: 0; /* Prevents overflow */ }

.article-header {
  border-bottom: 1px solid #ccc;
  margin-bottom: 15px;
  padding-bottom: 5px;
}
.article-header h2 {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 22px;
  color: #333;
  margin: 0 0 5px 0;
  font-weight: normal;
}
.article-meta {
  background-color: #e0e0e0;
  padding: 3px 5px;
  font-size: 10px;
  color: #666;
  margin-bottom: 15px;
  border-top: 1px solid #ccc;
  border-bottom: 1px solid #ccc;
}

/* --- "Article Tools" Box (Float Right) --- */
.article-tools {
  float: right;
  width: 160px;
  background: #f3f8fe;
  border: 1px solid #a3c4e6;
  margin: 0 0 15px 15px;
  padding: 0;
}
.article-tools h4 {
  background: #dcebfb;
  margin: 0;
  padding: 3px 5px;
  font-size: 10px;
  border-bottom: 1px solid #a3c4e6;
  color: #333;
}
.article-tools ul {
  list-style: none;
  padding: 5px;
  margin: 0;
}
.article-tools li {
  padding: 2px 0;
  font-size: 10px;
  background: url('data:image/gif;base64,R0lGODlhBAAEAJEAAAAAAP///+zq5////yH5BAEAAAMALAAAAAAEAAQAAAIDnI9WADs=') no-repeat 0 6px;
  padding-left: 10px;
}

/* =========================================
   FORM ELEMENTS & INPUT STYLES
   ========================================= */

/* Generic Input Styling (XP/2007 Style) */
input[type="text"],
input[type="password"],
input[type="email"],
input[type="url"],
input[type="search"],
input[type="number"],
input[type="tel"],
input[type="date"],
textarea,
select {
  border: 1px solid #7f9db9; /* Classic XP Blue Border */
  font-family: Verdana, Arial, Helvetica, sans-serif;
  font-size: 11px;
  padding: 3px;
  color: #333;
  background-color: #fff;
  vertical-align: middle;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #003399; /* Highlight color on focus */
  outline: none;
}

textarea {
  width: 98%;
  font-family: 'Courier New', Courier, monospace; /* Monospace for code/text editing */
  min-height: 80px;
}

/* Buttons (Generic & Submit) */
button,
input[type="submit"],
input[type="button"],
input[type="reset"],
.btn {
  background: #e0e0e0;
  background-image: linear-gradient(to bottom, #f0f0f0, #d0d0d0);
  border: 1px solid #888;
  color: #333;
  font-family: Verdana, Arial, sans-serif;
  font-size: 11px;
  padding: 2px 8px;
  cursor: pointer;
  border-radius: 2px;
  display: inline-block;
  text-decoration: none;
}

button:hover,
input[type="submit"]:hover,
.btn:hover {
  background-color: #ccc;
  border-color: #555;
  text-decoration: none;
  color: #000;
}

/* Primary Action Button (Blue) */
.btn-primary,
input[type="submit"].btn-primary {
  background-color: #2a81cf;
  background-image: linear-gradient(to bottom, #4f96d1, #2a81cf);
  color: white;
  border: 1px solid #145b9a;
  font-weight: bold;
}
.btn-primary:hover {
  background-color: #0b4da2;
  border-color: #003366;
  color: #fff;
}

/* Checkboxes & Radio Buttons */
input[type="checkbox"],
input[type="radio"] {
  vertical-align: middle;
  margin: 0 4px 0 0;
}

/* Fieldsets & Legends */
fieldset {
  border: 1px solid #ccc;
  padding: 10px;
  margin-bottom: 15px;
  background-color: #fcfcfc;
}
legend {
  font-weight: bold;
  color: #004a91;
  padding: 0 5px;
}

/* Form Layout Helpers */
.form-group {
  margin-bottom: 10px;
}
.form-label {
  display: block;
  font-weight: bold;
  margin-bottom: 3px;
  color: #444;
}
.form-hint {
  font-size: 10px;
  color: #666;
  margin-top: 2px;
}

/* =========================================
   EXTENDED BBCODE / CONTENT STYLES
   ========================================= */

/* 1. Blockquotes (Editor's Note style) */
blockquote {
  background-color: #f9f9f9;
  border-left: 3px solid #003366;
  margin: 15px 0;
  padding: 10px 15px;
  font-style: italic;
  font-family: Georgia, serif;
  color: #555;
}
blockquote p { margin: 0; }

/* 2. Code Blocks (The 'Terminal' look) */
pre {
  background: #f0f0f0;
  border: 1px solid #ccc;
  padding: 10px;
  overflow-x: auto;
  font-family: 'Consolas', 'Courier New', monospace;
  font-size: 12px;
  color: #000;
}
code {
  background: #eee;
  padding: 1px 3px;
  font-family: 'Consolas', monospace;
  color: #c00;
}

/* 3. Tables (Financial Data Style) */
table {
  width: 100%;
  border-collapse: collapse;
  margin: 15px 0;
  font-size: 11px;
  border: 1px solid #ccc;
}
table th {
  background: #e6eff9;
  color: #003366;
  font-weight: bold;
  text-align: left;
  padding: 5px 8px;
  border-bottom: 1px solid #9fbdda;
}
table td {
  padding: 5px 8px;
  border-bottom: 1px solid #eee;
}
table tr:nth-child(even) { background-color: #faffff; }
/* Specialized 'Ticker' coloring classes */
.diff-up { color: green; }
.diff-down { color: red; }

/* 4. Alert Boxes (Info/Warning) */
.msn-alert {
  border: 1px solid #ccc;
  padding: 10px;
  margin: 10px 0;
  display: flex;
  align-items: flex-start;
  gap: 10px;
}
.msn-alert.info {
  background-color: #f0f8ff;
  border-color: #bcd4e6;
}
.msn-alert.warning {
  background-color: #fff8dc;
  border-color: #e6dbb9;
}
.msn-alert-icon {
  font-weight: bold;
  font-size: 14px;
  line-height: 1;
}

/* 5. Images */
.content-image {
  border: 1px solid #ccc;
  padding: 2px;
  background: #fff;
  max-width: 100%;
  height: auto;
}
.image-caption {
  font-size: 10px;
  color: #666;
  margin-top: 2px;
  font-style: italic;
}

/* --- Footer --- */
footer {
  margin-top: 20px;
  border-top: 3px solid #003366;
  background: #f7f7f7;
  padding: 10px 15px;
  font-size: 10px;
  color: #666;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
footer a { color: #666; text-decoration: none; margin: 0 5px; }
footer a:hover { text-decoration: underline; }
footer .windows-logo { width: 14px; height: 14px; vertical-align: middle; margin-right: 2px; }
footer .left-links, footer .right-links { display: flex; align-items: center; white-space: nowrap; }
footer .copyright { flex-grow: 1; text-align: center; margin: 0 10px; }

/* --- Responsive --- */
@media (max-width: 768px) {
  #page-wrapper { width: 100%; border: none; }
  .container { grid-template-columns: 1fr; }
  .welcome-nav { grid-template-columns: 1fr; }
  .channel-nav { height: auto; padding-bottom: 5px; }
  .channel-nav ul { flex-wrap: wrap; }
  .article-tools { float: none; width: 100%; margin: 10px 0; }

  /* Stack footer on mobile */
  footer { flex-direction: column; gap: 10px; }
  footer .left-links, footer .right-links { justify-content: center; }

  /* --- HEADER MOBILE STACKING --- */
  .header {
    flex-direction: column;
    align-items: center;
  }
  .header .search-area {
    margin-top: 10px;
    width: 100%;
    box-sizing: border-box;
    display: flex;
    justify-content: center;
  }
  .header form {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: stretch;
  }
  .header form > div {
    /* Target the div holding input and button to make it flex */
    display: flex;
    gap: 5px;
  }
  .header input[type="search"] {
    width: 100% !important; /* Override the fixed width from desktop */
    flex: 1;
  }
}
</style>
</head>
<body>

<div id="page-wrapper">

  <div class="super-topbar">
    <div class="left-links">
      <span class="bold"><?php echo date('l, F d, Y'); ?></span>
    </div>
    <div class="right-links">
        <a href="babynames_admin.php">ADMIN</a>
    </div>
  </div>

  <div class="header">

    <div style="position: relative; display: inline-block;">

        <div id="canvas-ii0vyprf4" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; overflow: hidden; border-radius: 4px; background-color: #003366;"></div>

        <div class="logo" style="position: relative; z-index: 1; padding: 10px 20px;">
            <h1 style="color: #ffffff; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5); white-space: nowrap;">
                <a href="babynames.php" style="color: #fff; text-decoration: none;">BABY NAMES<span style="font-weight:normal; font-family:Arial; font-size:20px; color:#a3c4e6;">.unofficial</span></a>
            </h1>
        </div>

    </div>

    <div class="search-area">
            <form id="search_form" action="#" style="display:flex; flex-direction:column; gap:5px;">
                <label for="search_input" style="font-weight:bold; color:#444;">Search:</label>

                <div style="display:flex; gap:5px; position:relative;">
                    <input type="search" id="search_input" name="q" value="" style="flex:1; border: 1px solid #7f9db9; padding: 3px;" placeholder="Search name or definition..." autocomplete="off" />
                    <input type="submit" value="Search" class="btn-primary" style="padding: 2px 10px;" />
                    <div id="autocomplete-results" style="position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #7f9db9; z-index: 1000; display: none;"></div>
                </div>
            </form>
    </div>
  </div>

  <div class="channel-nav">
    <ul>
            <li>
            <a href="babynames.php">
                Home
            </a>
        </li>
            <li>
            <a href="babynames.php">
                All Names
            </a>
        </li>
    </ul>
  </div>

  <div class="container">

    <div class="sidebar-col">
      <div class="box">
        <h3>Navigation</h3>
        <div class="box-content">
          <ul class="sidebar-nav-list">
            <li><a href="babynames.php">Home</a></li>
            <li><a href="babynames.php#submit-form">Submit a Name</a></li>
          </ul>
        </div>
      </div>

      <div class="box">
          <h3>Recent</h3>
          <div class="box-content" id="sidebar-recent-names">
              <!-- Populated by JS -->
              Loading...
          </div>
      </div>
    </div>

    <div class="main-col">

        <?php if ($name): ?>
            <!-- Detail View -->
            <?php $currentUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
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
        <?php else: ?>
            <!-- Home View -->
            <?php if ($id > 0): ?>
                <div class="msn-alert warning">Name not found.</div>
            <?php endif; ?>

            <div class="box">
                <h3>Welcome</h3>
                <div class="box-content">
                    <p>Welcome to the Unofficial Baby Names directory. Here you can find unique and unofficial definitions for names.</p>
                </div>
            </div>

            <hr>

            <div class="box">
                <h3>Submit a Name</h3>
                <div class="box-content">
                    <form id="submit-name-form" class="form-container">
                        <div class="form-group">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name" required class="form-input" placeholder="e.g. Tony">
                        </div>
                        <div class="form-group">
                            <label for="definition" class="form-label">Definition</label>
                            <textarea name="definition" id="definition" required class="form-textarea" placeholder="e.g. An electrical tester for detecting voltage"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-primary">Submit Name</button>
                        </div>
                        <div id="submit-message" style="margin-top: 10px;"></div>
                    </form>
                </div>
            </div>

            <hr>

            <div class="box">
                <h3>Suggested Names</h3>
                <div class="box-content" id="suggested-names-list">
                    Loading...
                </div>
            </div>

            <hr>

            <div class="box">
                <h3>All Names</h3>
                <div class="box-content" id="all-names-list">
                    Loading...
                </div>
            </div>
        <?php endif; ?>

    </div>

</div> <!-- End Container -->

<!-- Footer -->
<footer>
  <div class="left-links">
    <a href="#">Privacy</a>|
    <a href="#">Legal</a>
  </div>
  <div class="copyright">
    &copy; <?php echo date('Y'); ?> Unofficial Baby Names
  </div>
</footer>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Polling function
    function loadNames() {
        // Load suggestions
        fetch('babynames.php?action=suggestions')
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('suggested-names-list');
                if (list) {
                    list.innerHTML = '';
                    if (data.length === 0) {
                        list.innerHTML = 'No suggestions yet.';
                        return;
                    }
                    const ul = document.createElement('ul');
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.innerHTML = `<a href="babynames.php?id=${item.id}"><b>${item.name}</b></a>: ${item.definition}`;
                        ul.appendChild(li);
                    });
                    list.appendChild(ul);
                }
            });

        // Load all names
        fetch('babynames.php?action=list')
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('all-names-list');
                const sidebarList = document.getElementById('sidebar-recent-names');

                if (list) {
                    list.innerHTML = '';
                    if (data.length === 0) {
                        list.innerHTML = 'No names found.';
                    } else {
                        const ul = document.createElement('ul');
                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.innerHTML = `<a href="babynames.php?id=${item.id}"><b>${item.name}</b></a>: ${item.definition}`;
                            ul.appendChild(li);
                        });
                        list.appendChild(ul);
                    }
                }

                if (sidebarList) {
                    sidebarList.innerHTML = '';
                     if (data.length === 0) {
                        sidebarList.innerHTML = 'Empty.';
                    } else {
                        const ul = document.createElement('ul');
                        ul.className = 'sidebar-nav-list';
                        // Show top 5
                        data.slice(0, 5).forEach(item => {
                            const li = document.createElement('li');
                            li.innerHTML = `<a href="babynames.php?id=${item.id}">${item.name}</a>`;
                            ul.appendChild(li);
                        });
                        sidebarList.appendChild(ul);
                    }
                }
            });
    }

    // Submit form
    const form = document.getElementById('submit-name-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                definition: formData.get('definition')
            };

            fetch('babynames.php?action=submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const msg = document.getElementById('submit-message');
                if (result.success) {
                    msg.innerHTML = '<span style="color:green">Name submitted successfully! Waiting for approval.</span>';
                    form.reset();
                    loadNames(); // Refresh lists
                } else {
                    msg.innerHTML = '<span style="color:red">Error: ' + result.error + '</span>';
                }
            });
        });
    }

    // Autocomplete
    const searchInput = document.getElementById('search_input');
    const resultsContainer = document.getElementById('autocomplete-results');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value;
            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }

            fetch(`babynames.php?action=search&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length > 0) {
                        resultsContainer.style.display = 'block';
                        const ul = document.createElement('ul');
                        ul.style.listStyle = 'none';
                        ul.style.padding = '5px';
                        ul.style.margin = '0';

                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.style.padding = '3px';
                            li.style.cursor = 'pointer';
                            li.innerHTML = `<b>${item.name}</b> - <span class="small-text">${item.definition.substring(0, 30)}...</span>`;
                            li.addEventListener('click', () => {
                                window.location.href = `babynames.php?id=${item.id}`;
                            });
                            li.onmouseover = function() { this.style.backgroundColor = '#eef'; };
                            li.onmouseout = function() { this.style.backgroundColor = '#fff'; };
                            ul.appendChild(li);
                        });
                        resultsContainer.appendChild(ul);
                    } else {
                        resultsContainer.style.display = 'none';
                    }
                });
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== searchInput) {
                resultsContainer.style.display = 'none';
            }
        });
    }

    // Initial load
    loadNames();

    // Poll every 30 seconds
    setInterval(loadNames, 30000);
});
</script>
</body>
</html>
