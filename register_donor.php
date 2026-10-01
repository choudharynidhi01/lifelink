<?php 
include 'db_connect.php'; 
include 'header.php'; 

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $age = intval($_POST['age']);
    $gender = trim($_POST['gender']);
    $blood_group = trim($_POST['blood_group']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $location = trim($_POST['location']);
    $address = trim($_POST['address']);
    $last_donation = !empty($_POST['last_donation_date']) ? $_POST['last_donation_date'] : NULL;

    $stmt = $conn->prepare("INSERT INTO donors (name, age, gender, blood_group, phone, email, location, address, last_donation_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssssss", $name, $age, $gender, $blood_group, $phone, $email, $location, $address, $last_donation);

    if ($stmt->execute()) {
        $message = "Thank you! You have successfully registered as a donor.";
        $msg_type = "success";
    } else {
        $message = "Registration failed: " . $stmt->error;
        $msg_type = "error";
    }
    $stmt->close();
}
?>

<!-- CDNs for icons, fonts, and animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    /* Background Canvas with Animated Floating Stickers */
    .donor-wrapper {
        position: relative;
        min-height: 85vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 15px;
        background: radial-gradient(circle at 10% 20%, rgba(225, 29, 72, 0.05) 0%, rgba(255, 255, 255, 0) 80%),
                    radial-gradient(circle at 90% 80%, rgba(159, 18, 57, 0.08) 0%, rgba(255, 255, 255, 0) 80%);
        overflow: hidden;
    }

    .floating-icon {
        position: absolute;
        color: #e11d48;
        opacity: 0.08;
        animation: floatAnim 8s ease-in-out infinite alternate;
        pointer-events: none;
        z-index: 0;
    }
    .f-icon-1 { top: 5%; left: 3%; font-size: 110px; animation-delay: 0s; }
    .f-icon-2 { bottom: 8%; right: 4%; font-size: 130px; animation-delay: 2s; }
    .f-icon-3 { top: 50%; left: 85%; font-size: 70px; animation-delay: 4s; }

    @keyframes floatAnim {
        0% { transform: translateY(0) rotate(0deg) scale(1); }
        100% { transform: translateY(-25px) rotate(12deg) scale(1.08); }
    }

    /* Modern Glass Card */
    .donor-card {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 720px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(225, 29, 72, 0.15);
        border-radius: 28px;
        padding: 45px 35px;
        box-shadow: 0 25px 50px -12px rgba(225, 29, 72, 0.15);
    }

    /* Form Header Banner */
    .form-header {
        text-align: center;
        margin-bottom: 35px;
    }
    .header-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffe4e6;
        color: #e11d48;
        font-weight: 700;
        font-size: 12px;
        padding: 6px 16px;
        border-radius: 20px;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .form-header h2 {
        font-size: 32px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.8px;
        margin-bottom: 8px;
    }
    .form-header p {
        color: #64748b;
        font-size: 15px;
    }

    /* Responsive Grid Layout */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .full-width {
        grid-column: span 2;
    }

    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
        .full-width { grid-column: span 1; }
        .donor-card { padding: 30px 20px; }
    }

    /* Custom Input Group Styling */
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
    .input-wrapper input, 
    .input-wrapper select, 
    .input-wrapper textarea {
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
    .input-wrapper textarea {
        padding-left: 16px;
        min-height: 90px;
        resize: vertical;
    }
    .input-wrapper input:focus, 
    .input-wrapper select:focus, 
    .input-wrapper textarea:focus {
        background: #ffffff;
        border-color: #e11d48;
        box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.12);
    }
    .input-wrapper input:focus + i.field-icon,
    .input-wrapper select:focus + i.field-icon {
        color: #e11d48;
    }

    /* Visual Radio Cards for Blood Group Selection */
    .blood-selector {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-top: 4px;
    }
    @media (max-width: 480px) {
        .blood-selector { grid-template-columns: repeat(4, 1fr); }
    }
    .blood-option {
        position: relative;
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
        transform: translateY(-2px);
    }
    .blood-badge:hover {
        border-color: #fb7185;
        background: #fff1f2;
    }

    /* Aesthetic Glowing Submit Button */
    .submit-btn {
        margin-top: 10px;
        width: 100%;
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        color: white;
        padding: 16px;
        border: none;
        border-radius: 14px;
        font-weight: 700;
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

    /* Notification Alerts */
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

<div class="donor-wrapper">
    <!-- Floating Decorative Background Stickers -->
    <i class="fa-solid fa-heart-pulse floating-icon f-icon-1"></i>
    <i class="fa-solid fa-hand-holding-medical floating-icon f-icon-2"></i>
    <i class="fa-solid fa-droplet floating-icon f-icon-3"></i>

    <div class="donor-card animate__animated animate__zoomIn">
        
        <div class="form-header">
            <span class="header-badge"><i class="fa-solid fa-shield-heart"></i> Save Lives Today</span>
            <h2>Become a Donor</h2>
            <p>Join our voluntary community. Your single donation can save up to 3 lives.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?> animate__animated animate__fadeIn">
                <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register_donor.php">
            <div class="form-grid">

                <!-- Full Name -->
                <div class="input-group">
                    <label><i class="fa-solid fa-user" style="color: #e11d48;"></i> Full Name *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-signature field-icon"></i>
                        <input type="text" name="name" placeholder="John Doe" required>
                    </div>
                </div>

                <!-- Age & Gender (Split Grid) -->
                <div class="input-group">
                    <label><i class="fa-solid fa-calendar-day" style="color: #e11d48;"></i> Age & Gender *</label>
                    <div style="display: flex; gap: 10px;">
                        <div class="input-wrapper" style="flex: 1;">
                            <i class="fa-solid fa-hashtag field-icon"></i>
                            <input type="number" name="age" min="18" max="65" placeholder="Age" required>
                        </div>
                        <div class="input-wrapper" style="flex: 1.2;">
                            <i class="fa-solid fa-venus-mars field-icon"></i>
                            <select name="gender" required>
                                <option value="" disabled selected>Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Select Blood Group Cards -->
                <div class="input-group full-width">
                    <label><i class="fa-solid fa-droplet" style="color: #e11d48;"></i> Select Blood Group *</label>
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

                <!-- Phone Number -->
                <div class="input-group">
                    <label><i class="fa-solid fa-phone" style="color: #e11d48;"></i> Phone Number *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-mobile-screen field-icon"></i>
                        <input type="tel" name="phone" placeholder="+1 234 567 890" required>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="input-group">
                    <label><i class="fa-solid fa-envelope" style="color: #e11d48;"></i> Email Address *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-at field-icon"></i>
                        <input type="email" name="email" placeholder="name@example.com" required>
                    </div>
                </div>

                <!-- City / Location -->
                <div class="input-group">
                    <label><i class="fa-solid fa-location-dot" style="color: #e11d48;"></i> City / Location *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-city field-icon"></i>
                        <input type="text" name="location" placeholder="e.g. New York, Downtown" required>
                    </div>
                </div>

                <!-- Last Donation Date -->
                <div class="input-group">
                    <label><i class="fa-solid fa-clock-rotate-left" style="color: #e11d48;"></i> Last Donation Date (Optional)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-calendar-check field-icon"></i>
                        <input type="date" name="last_donation_date">
                    </div>
                </div>

                <!-- Full Address -->
                <div class="input-group full-width">
                    <label><i class="fa-solid fa-map-pin" style="color: #e11d48;"></i> Full Residential Address *</label>
                    <div class="input-wrapper">
                        <textarea name="address" placeholder="Street address, building number, zip code..." required></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="full-width">
                    <button type="submit" class="submit-btn">
                        <i class="fa-solid fa-paper-plane"></i> Complete Registration
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>

</div>
</body>
</html>