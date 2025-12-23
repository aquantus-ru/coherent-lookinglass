<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$pdo = getDB();

if ($method === 'GET') {
    if ($action === 'search') {
        $query = isset($_GET['q']) ? $_GET['q'] : '';
        $stmt = $pdo->prepare("SELECT * FROM names WHERE status = 'approved' AND (name LIKE ? OR definition LIKE ?)");
        $term = '%' . $query . '%';
        $stmt->execute([$term, $term]);
        echo json_encode($stmt->fetchAll());

    } elseif ($action === 'suggestions') {
        $stmt = $pdo->query("SELECT * FROM names WHERE status = 'approved' ORDER BY RANDOM() LIMIT 5");
        echo json_encode($stmt->fetchAll());

    } else { // list
        $stmt = $pdo->query("SELECT * FROM names WHERE status = 'approved' ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
    }

} elseif ($method === 'POST') {
    // Handle JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'approve') {
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $stmt = $pdo->prepare("UPDATE names SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);

    } elseif ($action === 'reject') {
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $stmt = $pdo->prepare("UPDATE names SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);

    } else {
        // Submission - Public
        if ($input && isset($input['name']) && isset($input['definition'])) {
            $stmt = $pdo->prepare("INSERT INTO names (name, definition, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$input['name'], $input['definition']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
        }
    }
}
?>
