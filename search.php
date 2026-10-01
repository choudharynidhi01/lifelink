<?php 
include 'db_connect.php'; 
include 'header.php'; 

// Fetch filter parameters
$selected_group = isset($_GET['blood_group']) ? trim($_GET['blood_group']) : '';
$search_location = isset($_GET['location']) ? trim($_GET['location']) : '';

// Build realistic search query
$query = "SELECT *, DATEDIFF(CURRENT_DATE(), last_donation_date) AS days_since_donation FROM donors WHERE 1=1";
$params = [];
$types = "";

if (!empty($selected_group)) {
    $query .= " AND blood_group = ?";
    $params[] = $selected_group;
    $types .= "s";
}

if (!empty($search_location)) {
    $query .= " AND (location LIKE ? OR address LIKE ?)";
    $params[] = "%" . $search_location . "%";
    $params[] = "%" . $search_location . "%";
    $types .= "ss";
}

$query .= " ORDER BY id DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!-- CDNs for icons, fonts, and micro-interactions -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    /* Search Header Banner */
    .search-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 24px;
        padding: 40px 30px;
        color: white;
        margin-bottom: 35px;
        box-shadow: 0 15px 30px rgba(15, 23, 42, 0.15);
        position: relative;
        overflow: hidden;
    }

    .search-hero::after {
        content: '\f002';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: -20px;
        bottom: -30px;
        font-size: 180px;
        opacity: 0.04;
        color: white;
    }

    /* Filter Form Styling */
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
        margin-bottom: 35px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 15px;
        align-items: flex-end;
    }

    @media (max-width: 768px) {
        .filter-grid { grid-template-columns: 1fr; }
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-group label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
    }

    .filter-input {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s;
        background: #f8fafc;
    }

    .filter-input:focus {
        border-color: #e11d48;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1);
    }

    /* Blood Chips Selector */
    .blood-chips {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .chip {
        padding: 6px 14px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s;
    }

    .chip:hover, .chip.active {
        background: #e11d48;
        color: white;
        border-color: #e11d48;
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
    }

    /* Donor Layout Grid & Sidebar */
    .directory-layout {
        display: grid;
        grid-template-columns: 2.2fr 1fr;
        gap: 30px;
    }

    @media (max-width: 992px) {
        .directory-layout { grid-template-columns: 1fr; }
    }

    /* Donor Cards Layout */
    .donor-card-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 22px;
        margin-bottom: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    .donor-card-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        border-color: #fecdd3;
    }

    @media (max-width: 640px) {
        .donor-card-item { flex-direction: column; align-items: flex-start; }
    }

    .donor-avatar-group {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .blood-badge-large {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        color: white;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(225, 29, 72, 0.3);
        flex-shrink: 0;
    }

    .donor-info h3 {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-eligible { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .status-cooling { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    .donor-details {
        display: flex;
        gap: 18px;
        font-size: 13px;
        color: #64748b;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .donor-details span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Action Buttons Group */
    .donor-actions {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }

    .btn-action {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        text-decoration: none;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }

    .btn-call { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .btn-call:hover { background: #2563eb; color: white; }

    .btn-wa { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
    .btn-wa:hover { background: #16a34a; color: white; }

    .btn-mail { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .btn-mail:hover { background: #e11d48; color: white; }

    /* Emergency Sidebar */
    .sidebar-widget {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    .request-mini-card {
        padding: 14px 0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .request-mini-card:last-child { border-bottom: none; }

    .urgency-pill {
        font-size: 10px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 6px;
        color: white;
    }
    .urgency-Critical { background: #e11d48; }
    .urgency-Urgent { background: #f59e0b; }
    .urgency-Normal { background: #3b82f6; }
</style>

<!-- Hero Section -->
<div class="search-hero animate__animated animate__fadeIn">
    <span style="background: rgba(255,255,255,0.15); font-size: 12px; padding: 4px 12px; border-radius: 20px; font-weight: 700;">
        <i class="fa-solid fa-satellite-dish" style="color: #4ade80;"></i> LIVE REGISTRY DIRECTORY
    </span>
    <h1 style="font-size: 32px; font-weight: 800; margin-top: 10px; letter-spacing: -0.5px;">Search Verified Donors</h1>
    <p style="color: #94a3b8; font-size: 15px; margin-top: 4px;">Locate active voluntary blood donors by blood group or location in real-time.</p>
</div>

<!-- Interactive Search Filters -->
<div class="filter-card">
    <form method="GET" action="search.php" class="filter-grid">
        <div class="filter-group">
            <label><i class="fa-solid fa-filter" style="color: #e11d48;"></i> Blood Group</label>
            <select name="blood_group" class="filter-input">
                <option value="">All Blood Groups</option>
                <?php 
                $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                foreach ($groups as $bg) {
                    $sel = ($selected_group === $bg) ? 'selected' : '';
                    echo "<option value='$bg' $sel>$bg</option>";
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label><i class="fa-solid fa-location-dot" style="color: #e11d48;"></i> City or Area</label>
            <input type="text" name="location" class="filter-input" placeholder="e.g. Downtown, New York..." value="<?php echo htmlspecialchars($search_location); ?>">
        </div>

        <div>
            <button type="submit" class="btn" style="padding: 12px 24px; border-radius: 12px; font-weight: 700;">
                <i class="fa-solid fa-magnifying-glass"></i> Filter Results
            </button>
        </div>
    </form>

    <!-- Fast Click Quick Group Chips -->
    <div class="blood-chips">
        <span style="font-size: 12px; font-weight: 700; color: #94a3b8; align-self: center;">Quick Select:</span>
        <a href="search.php" class="chip <?php echo empty($selected_group) ? 'active' : ''; ?>">All</a>
        <?php foreach ($groups as $bg): ?>
            <a href="search.php?blood_group=<?php echo urlencode($bg); ?>&location=<?php echo urlencode($search_location); ?>" 
               class="chip <?php echo ($selected_group === $bg) ? 'active' : ''; ?>">
                <?php echo $bg; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Main Directory Grid Layout -->
<div class="directory-layout">

    <!-- Donor Results Column -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a;">
                Available Donors 
                <span style="font-size: 13px; font-weight: 600; color: #64748b;">(<?php echo $result->num_rows; ?> found)</span>
            </h3>
            <a href="search.php" style="font-size: 13px; color: #e11d48; text-decoration: none; font-weight: 600;">Reset Filters</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                    // Calculate donor eligibility status (e.g., 90 days required between donations)
                    $days = $row['days_since_donation'];
                    $is_eligible = ($days === null || $days >= 90);
                    $clean_phone = preg_replace('/[^0-9]/', '', $row['phone']);
                ?>
                <div class="donor-card-item animate__animated animate__fadeInUp">
                    <div class="donor-avatar-group">
                        <div class="blood-badge-large">
                            <?php echo htmlspecialchars($row['blood_group']); ?>
                        </div>
                        <div class="donor-info">
                            <h3>
                                <?php echo htmlspecialchars($row['name']); ?>
                                <?php if ($is_eligible): ?>
                                    <span class="status-badge status-eligible"><i class="fa-solid fa-circle-check"></i> Ready to Donate</span>
                                <?php else: ?>
                                    <span class="status-badge status-cooling"><i class="fa-solid fa-clock"></i> In Cooling Period</span>
                                <?php endif; ?>
                            </h3>

                            <div class="donor-details">
                                <span><i class="fa-solid fa-location-dot" style="color: #e11d48;"></i> <?php echo htmlspecialchars($row['location']); ?></span>
                                <span><i class="fa-solid fa-user" style="color: #64748b;"></i> <?php echo htmlspecialchars($row['gender']); ?>, <?php echo htmlspecialchars($row['age']); ?> yrs</span>
                                <span><i class="fa-solid fa-calendar" style="color: #64748b;"></i> Last: <?php echo $row['last_donation_date'] ? htmlspecialchars($row['last_donation_date']) : 'First Time'; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Contact Actions -->
                    <div class="donor-actions">
                        <a href="tel:<?php echo htmlspecialchars($row['phone']); ?>" class="btn-action btn-call" title="Call Donor">
                            <i class="fa-solid fa-phone"></i>
                        </a>
                        <a href="https://wa.me/<?php echo $clean_phone; ?>?text=Hello%20<?php echo urlencode($row['name']); ?>,%20I%20found%20your%20profile%20on%20LifeLink%20and%20need%20urgent%20blood%20assistance." 
                           target="_blank" class="btn-action btn-wa" title="WhatsApp Direct Message">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="btn-action btn-mail" title="Send Email">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; background: white; padding: 50px 20px; border-radius: 20px; border: 1px solid #e2e8f0;">
                <i class="fa-solid fa-user-slash" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                <h4 style="font-size: 18px; color: #334155;">No Donors Found</h4>
                <p style="color: #94a3b8; font-size: 14px; margin-top: 5px;">Try clearing filters or searching for a broader location.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Emergency Patients Sidebar -->
    <div>
        <div class="sidebar-widget">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-truck-medical" style="color: #e11d48;"></i> Emergency Patient Requests
            </h3>

            <?php 
            $req_res = $conn->query("SELECT * FROM blood_requests WHERE status = 'Pending' ORDER BY id DESC LIMIT 4");
            if ($req_res && $req_res->num_rows > 0):
                while ($req = $req_res->fetch_assoc()):
            ?>
                <div class="request-mini-card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong style="font-size: 14px; color: #0f172a;"><?php echo htmlspecialchars($req['patient_name']); ?></strong>
                        <span class="urgency-pill urgency-<?php echo htmlspecialchars($req['urgency']); ?>">
                            <?php echo htmlspecialchars($req['urgency']); ?>
                        </span>
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                        Need: <strong style="color: #e11d48;"><?php echo htmlspecialchars($req['units_required']); ?> Units (<?php echo htmlspecialchars($req['blood_group']); ?>)</strong>
                    </div>
                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                        <i class="fa-solid fa-hospital"></i> <?php echo htmlspecialchars($req['hospital_name']); ?>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <p style="font-size: 13px; color: #94a3b8;">No pending emergency requests.</p>
            <?php endif; ?>

            <a href="request_blood.php" class="btn" style="width: 100%; text-align: center; margin-top: 15px; padding: 10px; border-radius: 10px; font-size: 13px;">
                + Post Emergency Request
            </a>
        </div>
    </div>

</div>

</div>
</body>
</html>