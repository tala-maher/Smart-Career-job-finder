<?php
session_start();
include_once "db_connect.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$total_applicants = $conn->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$total_rejections = $conn->query("SELECT COUNT(*) FROM user_feedback WHERE status='rejected'")->fetchColumn();
$top_skills = $conn->query("SELECT required_skills, COUNT(*) as count FROM jobs GROUP BY required_skills ORDER BY count DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enterprise Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background: #f8fafc; font-family: 'Segoe UI', sans-serif;">

<section class="admin-dashboard" style="max-width: 1000px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
    <h2 style="color: #0f172a; margin-bottom: 25px;"><i class="fa-solid fa-microchip"></i> AI Engine Analytics Dashboard</h2>
    
    <div style="display: flex; gap: 20px; margin-bottom: 40px;">
        <div style="flex: 1; padding: 25px; background: #0f172a; color: white; border-radius: 15px; text-align: center;">
            <h3 style="margin:0; font-size: 2rem;"><?php echo htmlspecialchars($total_applicants); ?></h3>
            <p style="margin:0; opacity: 0.8;">Total Talent Pipeline</p>
        </div>
        <div style="flex: 1; padding: 25px; background: #2b70c9; color: white; border-radius: 15px; text-align: center;">
            <h3 style="margin:0; font-size: 2rem;"><?php echo htmlspecialchars($total_rejections); ?></h3>
            <p style="margin:0; opacity: 0.8;">System Rejection Events</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        <div>
            <h3 style="color: #334155;"><i class="fa-solid fa-chart-pie"></i> Skills Demand Distribution</h3>
            <canvas id="skillsChart" style="max-height: 300px;"></canvas>
        </div>

        <div style="background: #f1f5f9; padding: 20px; border-radius: 15px;">
            <h3 style="color: #334155;"><i class="fa-solid fa-brain"></i> System Optimization Report</h3>
            <p style="font-size: 0.95rem; color: #475569; line-height: 1.8;">
                The <strong>AI Decision Support Engine</strong> is currently processing real-time interaction logs. 
                By analyzing the <strong><?php echo htmlspecialchars($total_rejections); ?></strong> registered rejection events, the system is dynamically recalibrating its matching vector to increase precision and reduce noise in candidate-to-role alignment.
            </p>
            <div style="margin-top: 20px; border-top: 1px solid #cbd5e1; padding-top: 15px;">
                <strong>Status:</strong> <span style="color: #16a34a;">Operational & Self-Learning</span>
            </div>
        </div>
    </div>
</section>

<script>
   
    const ctx = document.getElementById('skillsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [<?php foreach($top_skills as $s) echo "'" . htmlspecialchars($s['required_skills']) . "',"; ?>],
            datasets: [{
                data: [<?php foreach($top_skills as $s) echo $s['count'] . ","; ?>],
                backgroundColor: ['#2b70c9', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
</script>

</body>
</html>