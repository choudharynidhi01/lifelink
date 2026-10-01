<?php 
include 'db_connect.php'; 
include 'header.php'; 

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_name = trim($_POST['patient_name']);
    $blood_group  = trim($_POST['blood_group']);
    $units        = intval($_POST['units_required']);
    $hospital     = trim($_POST['hospital_name']);
    $location     = trim($_POST['location']);
    $contact      = trim($_POST['contact_number']);
    $urgency      = trim($_POST['urgency']);

    $stmt = $conn->prepare("INSERT INTO blood_requests (patient_name, blood_group, units_required, hospital_name, location, contact_number, urgency) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $patient_name, $blood_group, $units, $hospital, $location, $contact, $urgency);

    if ($stmt->execute()) {
        $message = "Emergency Blood Request posted successfully! Our network has been alerted.";
        $msg_type = "success";
    } else {
        $message = "Failed to post request: " . $stmt->error;
        $msg_type = "error";
    }
    $stmt->close();
}
?>

<!-- CDNs for icons, fonts, and animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    /* Animated Glowing Canvas */
    .request-wrapper {
        position: relative;
        min-height: 85vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 15px;
        background: radial-gradient(circle at 50% 30%, rgba(225, 29, 72, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
        overflow: hidden;
    }

    /* Floating Animated Icons */
    .pulse-sticker {
        position: absolute;
        color: #e11d48;
        opacity: 0.1;
        animation: heartPulse 2.5s ease-in-out infinite alternate;
        pointer-events: none;
        z-index: 0;
    }
    .sticker-left { top: 12%; left: 4%; font-size: 110px; }
    .sticker-right { bottom: 10%; right: 4%; font-size: 120px; animation-delay: 1.2s; }

    @keyframes heartPulse {
        0% { transform: scale(1) rotate(0deg); opacity: 0.08; }
        50% { transform: scale(1.15) rotate(6deg); opacity: 0.16; }
        100% { transform: scale(1) rotate(0deg); opacity: 0.08; }
    }

    /* Glassmorphism Card Container */
    .request-card {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 740px;
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(225, 29, 72, 0.18);
        border-radius: 28px;
        padding: 45px 35px;
        box-shadow: 0 25px 50px -12px rgba(225, 29, 72, 0.18);
    }

    /* Header Banner */
    .form-header {
        text-align: center;
        margin-bottom: 35px;
    }
    
    .emergency-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffe4e6;
        color: #e11d48;
        font-weight: 800;
        font-size: 12px;
        padding: 6px 16px;
        border-radius: 20px;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        animation: flash 2s infinite;
    }

    .form-header h2 {
        font-size: 34px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -1px;
        margin-bottom: 8px;
    }

    /* Responsive Grid Layout */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
    }
    .full-width { grid-column: span 2; }

    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
        .full-width { grid-column: span 1; }
        .request-card { padding: 30px 20px; }
    }

    /* Field Inputs */
    .input-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .input-group label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-wrapper i.field-icon {
        position: absolute;
        left: 16px;
        color: #94a3b8;
        font-size: 15px;
        transition: color 0.3s;
    }
    .input-wrapper input, .input-wrapper select {
        width: 100%;
        padding: 13px 16px 13px 44px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        font-size: 14px;
        color: #0f172a;
        transition: all 0.3s ease;
        outline: none;
    }
    .input-wrapper input:focus, .input-wrapper select:focus {
        background: #ffffff;
        border-color: #e11d48;
        box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.12);
    }
    .input-wrapper input:focus + i.field-icon, .input-wrapper select:focus + i.field-icon {
        color: #e11d48;
    }

    /* Visual Interactive Urgency Level Cards */
    .urgency-selector {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-top: 4px;
    }
    .urgency-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    .urgency-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 14px 10px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        font-weight: 700;
        font-size: 13px;
        color: #475569;
        cursor: pointer;
        transition: all 0.25s ease;
        gap: 6px;
    }
    
    /* Active Urgency Styles */
    .urgency-option input[value="Normal"]:checked + .urgency-card {
        background: #eff6ff; color: #2563eb; border-color: #3b82f6; box-shadow: 0 6px 15px rgba(59, 130, 246, 0.25);
    }
    .urgency-option input[value="Urgent"]:checked + .urgency-card {
        background: #fffbebf; color: #d97706; border-color: #f59e0b; box-shadow: 0 6px 15px rgba(245, 158, 11, 0.25);
    }
    .urgency-option input[value="Critical"]:checked + .urgency-card {
        background: #ffe4e6; color: #e11d48; border-color: #e11d48; box-shadow: 0 6px 15px rgba(225, 29, 72, 0.3); animation: headShake 1s;
    }

    /* Visual Blood Selector Chips */
    .blood-selector {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-top: 4px;
    }
    .blood-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    .blood-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px 6px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-weight: 800;
        font-size: 15px;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .blood-option input[type="radio"]:checked + .blood-badge {
        background: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
        box-shadow: 0 6px 15px rgba(225, 29, 72, 0.3);
    }

    /* Glowing Submit Button */
    .submit-btn {
        margin-top: 10px;
        width: 100%;
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        color: white;
        padding: 16px;
        border: none;
        border-radius: 14px;
        font-weight: 800;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 10px 25px rgba(225, 29, 72, 0.35);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(225, 29, 72, 0.5);
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    }

    /* Notification Banner */
    .alert {
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-error { background: #ffe4e6; color: #be123c; border: 1px solid #fecdd3; }
</style>

<div class="request-wrapper">
    <!-- Pulse Decorative Background Icons -->
    <i class="fa-solid fa-truck-medical pulse-sticker sticker-left"></i>
    <i class="fa-solid fa-heart-pulse pulse-sticker sticker-right"></i>

    <div class="request-card animate__animated animate__zoomIn">
        
        <div class="form-header">
            <span class="emergency-badge"><i class="fa-solid fa-bell"></i> Instant Emergency Alert</span>
            <h2>Post Blood Request</h2>
            <p style="color: #64748b; font-size: 15px;">Broadcast your emergency requirement immediately across our donor network.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?> animate__animated animate__fadeIn">
                <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="request_blood.php">
            <div class="form-grid">

                <!-- Patient Name -->
                <div class="input-group">
                    <label><i class="fa-solid fa-bed-pulse" style="color: #e11d48;"></i> Patient Name *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user-injured field-icon"></i>
                        <input type="text" name="patient_name" placeholder="e.g. Sarah Jenkins" required>
                    </div>
                </div>

                <!-- Units Required -->
                <div class="input-group">
                    <label><i class="fa-solid fa-vials" style="color: #e11d48;"></i> Units Needed *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-hashtag field-icon"></i>
                        <input type="number" name="units_required" min="1" max="20" placeholder="e.g. 2" required>
                    </div>
                </div>

                <!-- Blood Group Selection Cards -->
                <div class="input-group full-width">
                    <label><i class="fa-solid fa-droplet" style="color: #e11d48;"></i> Required Blood Group *</label>
                    <div class="blood-selector">
                        <?php 
                        $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                        foreach ($groups as $index => $bg): 
                        ?>
                            <label class="blood-option">
                                <input type="radio" name="blood_group" value="<?php echo $bg; ?>" <?php echo $index === 0 ? 'required' : ''; ?>>
                                <span class="blood-badge"><?php echo $bg; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Urgency Level Cards -->
                <div class="input-group full-width">
                    <label><i class="fa-solid fa-triangle-exclamation" style="color: #e11d48;"></i> Urgency Status *</label>
                    <div class="urgency-selector">
                        <label class="urgency-option">
                            <input type="radio" name="urgency" value="Normal" checked>
                            <span class="urgency-card">
                                <i class="fa-solid fa-clock" style="font-size: 18px;"></i>
                                Normal Priority
                            </span>
                        </label>

                        <label class="urgency-option">
                            <input type="radio" name="urgency" value="Urgent">
                            <span class="urgency-card">
                                <i class="fa-solid fa-bolt" style="font-size: 18px;"></i>
                                Urgent (Within 24h)
                            </span>
                        </label>

                        <label class="urgency-option">
                            <input type="radio" name="urgency" value="Critical">
                            <span class="urgency-card">
                                <i class="fa-solid fa-circle-radiation" style="font-size: 18px;"></i>
                                CRITICAL (Immediate)
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Hospital Name -->
                <div class="input-group">
                    <label><i class="fa-solid fa-hospital" style="color: #e11d48;"></i> Hospital Name *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-square-h field-icon"></i>
                        <input type="text" name="hospital_name" placeholder="e.g. St. Jude General Hospital" required>
                    </div>
                </div>

                <!-- City / Area -->
                <div class="input-group">
                    <label><i class="fa-solid fa-location-dot" style="color: #e11d48;"></i> City / Location *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-city field-icon"></i>
                        <input type="text" name="location" placeholder="e.g. Brooklyn, Ward 4" required>
                    </div>
                </div>

                <!-- Contact Number -->
                <div class="input-group full-width">
                    <label><i class="fa-solid fa-phone" style="color: #e11d48;"></i> Emergency Contact Number *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-mobile-screen field-icon"></i>
                        <input type="tel" name="contact_number" placeholder="+1 234 567 890" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="full-width">
                    <button type="submit" class="submit-btn">
                        <i class="fa-solid fa-paper-plane"></i> Broadcast Emergency Request Now
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>

</div>
</body>
</html>