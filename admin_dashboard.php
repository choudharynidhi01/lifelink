<?php 
include 'header.php'; 
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle Blood Stock Update
if (isset($_POST['update_stock'])) {
    $stock_id = intval($_POST['stock_id']);
    $units = intval($_POST['units']);
    $stmt = $conn->prepare("UPDATE blood_stock SET units_available = ? WHERE id = ?");
    $stmt->bind_param("ii", $units, $stock_id);
    $stmt->execute();
}

// Handle Request Status Update
if (isset($_GET['action']) && isset($_GET['req_id'])) {
    $req_id = intval($_GET['req_id']);
    $status = ($_GET['action'] === 'approve') ? 'Approved' : 'Rejected';
    $stmt = $conn->prepare("UPDATE blood_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $req_id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}
?>

<h2>Admin Command Dashboard</h2>
<p class="subtitle">Manage real-time inventory, incoming requests, and voluntary donors.</p>

<div class="card">
    <h3>Blood Stock Management</h3>
    <table>
        <thead>
            <tr>
                <th>Blood Group</th>
                <th>Available Units</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stocks = $conn->query("SELECT * FROM blood_stock");
            while ($st = $stocks->fetch_assoc()):
            ?>
                <tr>
                    <td><strong><?php echo $st['blood_group']; ?></strong></td>
                    <form method="POST" action="">
                        <td>
                            <input type="number" name="units" value="<?php echo $st['units_available']; ?>" style="width: 90px;" min="0">
                            <input type="hidden" name="stock_id" value="<?php echo $st['id']; ?>">
                        </td>
                        <td>
                            <button type="submit" name="update_stock" class="btn" style="padding: 6px 14px; font-size: 12px;">Save</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Blood Requests Overview</h3>
    <table>
        <thead>
            <tr>
                <th>Patient Details</th>
                <th>Group</th>
                <th>Units</th>
                <th>Hospital</th>
                <th>Urgency</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $reqs = $conn->query("SELECT * FROM blood_requests ORDER BY id DESC");
            while ($r = $reqs->fetch_assoc()):
            ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['patient_name']); ?></strong><br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($r['contact_number']); ?></small></td>
                    <td><strong><?php echo htmlspecialchars($r['blood_group']); ?></strong></td>
                    <td><?php echo $r['units_required']; ?></td>
                    <td><?php echo htmlspecialchars($r['hospital_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['urgency']); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($r['status']); ?>"><?php echo $r['status']; ?></span></td>
                    <td>
                        <?php if($r['status'] === 'Pending'): ?>
                            <a href="?action=approve&req_id=<?php echo $r['id']; ?>" class="btn" style="padding: 6px 12px; font-size: 12px; background: #16a34a;">Approve</a>
                            <a href="?action=reject&req_id=<?php echo $r['id']; ?>" class="btn" style="padding: 6px 12px; font-size: 12px; background: #dc2626;">Reject</a>
                        <?php else: ?>
                            <span style="font-size: 12px; color: var(--text-muted);">Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>