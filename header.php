<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeLink — Blood Donor & Management Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e63946;
            --primary-dark: #c1121f;
            --primary-light: #ffccd5;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 14px;
            --shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        
        /* Modern Floating Header */
        header { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(12px); 
            border-bottom: 1px solid var(--border); 
            padding: 16px 8%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: sticky; 
            top: 0; 
            z-index: 100; 
        }
        .brand { font-size: 22px; font-weight: 700; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .brand span { color: var(--text-main); }
        
        nav { display: flex; gap: 24px; align-items: center; }
        nav a { color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 14px; transition: all 0.2s; }
        nav a:hover { color: var(--primary); }
        nav a.btn-nav { background: var(--primary); color: white; padding: 8px 16px; border-radius: 8px; }
        nav a.btn-nav:hover { background: var(--primary-dark); color: white; }

        .container { max-width: 1100px; width: 90%; margin: 40px auto; flex: 1; }
        
        /* Glassmorphism Cards */
        .card { 
            background: var(--surface); 
            border: 1px solid var(--border); 
            border-radius: var(--radius); 
            padding: 32px; 
            box-shadow: var(--shadow); 
            margin-bottom: 28px; 
        }
        
        h2 { font-size: 24px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; }
        p.subtitle { color: var(--text-muted); margin-bottom: 24px; font-size: 14px; }

        /* Modern UI Controls */
        .btn { 
            display: inline-block; 
            padding: 12px 24px; 
            background: var(--primary); 
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-weight: 600; 
            cursor: pointer; 
            text-decoration: none; 
            font-size: 14px; 
            transition: transform 0.2s, background 0.2s;
        }
        .btn:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-secondary { background: #f1f5f9; color: var(--text-main); }
        .btn-secondary:hover { background: #e2e8f0; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; 
            padding: 12px 16px; 
            border: 1px solid var(--border); 
            border-radius: 10px; 
            background: #f8fafc; 
            font-size: 14px; 
            outline: none; 
            transition: border 0.2s; 
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--primary); background: #fff; }

        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 16px; }

        /* Tables & Badges */
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 16px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border); }
        table th { background: #f8fafc; padding: 14px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-muted); text-align: left; border-bottom: 1px solid var(--border); }
        table td { padding: 16px; border-bottom: 1px solid var(--border); font-size: 14px; }
        table tr:last-child td { border-bottom: none; }

        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #ffe4e6; color: #9f1239; }

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #ffe4e6; color: #9f1239; }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="brand">🩸 Life<span>Link</span></a>
        <nav>
            <a href="index.php">Home</a>
            <a href="register_donor.php">Register Donor</a>
            <a href="search.php">Search Blood</a>
            <a href="request_blood.php">Request Blood</a>
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
                <a href="admin_dashboard.php" class="btn-nav">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="admin_login.php" class="btn-nav">Admin Portal</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">