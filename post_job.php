<?php
session_start();

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

require_once 'db_connect.php';

$check_stmt = $conn->prepare("SELECT role FROM users WHERE id = :id");
$check_stmt->execute(['id' => $_SESSION['user_id']]);
$current_user_role = strtolower(trim($check_stmt->fetchColumn() ?: ''));

if ($current_user_role !== 'company' && $current_user_role !== 'recruiter') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $company_name = trim($_POST['company_name'] ?? 'My Company');
    $city = trim($_POST['city']);
    
    $category = trim($_POST['category']);
    $job_type = trim($_POST['job_type']);
    
    $required_skills = strtolower(trim($_POST['required_skills']));
    $company_id = $_SESSION['user_id'];

    $sql = "INSERT INTO jobs (title, company, city, category, job_type, required_skills, company_id) 
            VALUES (:title, :company, :city, :category, :job_type, :required_skills, :company_id)";
            
    $stmt = $conn->prepare($sql);
    $execution = $stmt->execute([
        'title'           => $title,
        'company'         => $company_name,
        'city'            => $city,
        'category'        => $category,
        'job_type'        => $job_type,
        'required_skills' => $required_skills,
        'company_id'      => $company_id
    ]);

    if ($execution) {
       
        header("Location: manage_jobs.php?status=posted_success");
        exit();
    } else {
        echo "<script>alert('Failed to post the job. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Job | Smart Career</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>

<main style="max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    <h2 style="margin-bottom: 10px;"><i class="fa-solid fa-circle-plus" style="color: #2b70c9;"></i> Post a New Position</h2>
    <p style="color: #64748b; margin-bottom: 25px;">Fill in the specific parameters for the AI Match Engine to aggregate candidates.</p>

    <form action="post_job.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
        
        <div>
            <label style="font-weight: bold; display:block; margin-bottom: 5px;">Job Title</label>
            <input type="text" name="title" required style="width:100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div>
            <label style="font-weight: bold; display:block; margin-bottom: 5px;">Company Name</label>
            <input type="text" name="company_name" required style="width:100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div>
            <label style="font-weight: bold; display:block; margin-bottom: 5px;">City / Location</label>
            <input type="text" name="city" required style="width:100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>

        <div>
            <label style="font-weight: bold; display:block; margin-bottom: 5px;">Job Category</label>
            <select name="category" required style="width:100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: white;">
                <option value="Development">Development / Software Engineering</option>
                <option value="Data Science">Data Science & AI</option>
                <option value="Cybersecurity">Cybersecurity & Networking</option>
                <option value="Design">UI/UX & Graphic Design</option>
            </select>
        </div>

        <div>
            <label style="font-weight: bold; display:block; margin-bottom: 5px;">Job Type</label>
            <select name="job_type" required style="width:100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: white;">
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Remote">Remote</option>
                <option value="Internship">Internship</option>
            </select>
        </div>

        <div>
            <label style="font-weight: bold; display:block; margin-bottom: 5px;">Required Skills (Comma-separated)</label>
            <input type="text" name="required_skills" placeholder="e.g. python, php, sql, java" required style="width:100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            <small style="color: #94a3b8;">Crucial for AI Engine calculations.</small>
        </div>

        <button type="submit" style="background: #2b70c9; color: white; padding: 12px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; font-size: 1rem;">
            Publish Position
        </button>
    </form>
</main>

</body>
</html>