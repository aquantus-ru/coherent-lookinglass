<?php
$pdo = getDB();
$pending = $pdo->query("SELECT * FROM names WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
$all = $pdo->query("SELECT * FROM names ORDER BY created_at DESC")->fetchAll();
?>

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
                    <tr>
                        <td colspan="4">No pending submissions.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h4 style="margin-top: 20px;">All Names</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Definition</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
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
    fetch('api.php?action=approve&id=' + id, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
}

function rejectName(id) {
    fetch('api.php?action=reject&id=' + id, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
}

function deleteName(id) {
    if(confirm('Are you sure?')) {
        fetch('api.php?action=reject&id=' + id, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) location.reload();
            });
    }
}
</script>
