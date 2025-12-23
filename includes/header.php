<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? $pageTitle : 'Unofficial Baby Names'; ?> | COHERENT</title>
<link rel="stylesheet" href="static/style.css">
</head>
<body>

<div id="page-wrapper">

  <div class="super-topbar">
    <div class="left-links">
      <span class="bold"><?php echo date('l, F d, Y'); ?></span>
    </div>
    <div class="right-links">
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a href="babynames_admin.php?logout=1">LOGOUT</a>
        <?php else: ?>
            <a href="babynames_admin.php">ADMIN</a>
        <?php endif; ?>
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
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <li><a href="babynames_admin.php"><b>Admin Dashboard</b></a></li>
            <?php endif; ?>
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
