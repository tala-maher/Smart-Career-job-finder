<?php
session_start();

// Redirect to dashboard if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Smart Career</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once "navbar.php"; ?>


<section class="signup-page">
    <div class="signup-card">
        <h2>Create Your Account</h2>
        <p>Enter your information and let Smart Career help you find your perfect future job.</p>

        <form action="register_process.php" method="POST" enctype="multipart/form-data">
            <div class="grid-form">
                
                <div class="input-box full-width" style="margin-bottom: 15px;">
                    <label style="font-weight: 700; color: var(--primary-color, #0066cc);">Account Type (I want to...)</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user-gear"></i>
                        <select name="role" id="roleSelect" required style="width: 100%; padding: 12px 40px; border: 1px solid #ccc; border-radius: 8px; font-family: inherit; background: white; cursor: pointer;">
                            <option value="job_seeker">Look for a Dream Job (Job Seeker)</option>
                            <option value="recruiter">Post Jobs & Hire Talents (Recruiter)</option>
                        </select>
                    </div>
                </div>

                <div class="input-box">
                    <label>Full Name</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" name="name" placeholder="Enter your full name" required>
                    </div>
                </div>

                <div class="input-box seeker-only">
                    <label>Career / Job Title</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-briefcase"></i>
                        <input type="text" name="career" id="careerInput" placeholder="e.g. Front-End Developer" required>
                    </div>
                </div>

                <div class="input-box">
                    <label>Email</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="input-box">
                    <label>Profile Picture (Optional)</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-image"></i>
                        <input type="file" name="profile_pic" accept="image/*">
                    </div>
                </div>

                <div class="input-box">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Create password" required>
                    </div>
                </div>

                <div class="input-box">
                    <label>Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-shield-halved"></i>
                        <input type="password" name="confirm_password" placeholder="Repeat password" required>
                    </div>
                </div>

                <div class="input-box seeker-only">
                    <label>Skills</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-code"></i>
                        <input type="text" name="skills" id="skillsInput" placeholder="HTML, CSS, JavaScript" required>
                    </div>
                </div>

                <div class="input-box seeker-only">
                    <label>Location</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" name="location" id="locationInput" placeholder="Enter your city/country" required>
                    </div>
                </div>

                <div class="input-box full-width seeker-only">
                    <label>Upload CV</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-file-lines"></i>
                        <input type="file" name="cv" id="cvInput" accept=".pdf,.doc,.docx" required>
                    </div>
                </div>
            </div>

            <div class="button-container">
                <button type="submit" class="find-btn">
                    <span>REGISTER ACCOUNT</span>
                    <img src="images/person.png" alt="person" class="btn-person-img">
                </button>
            </div>
        </form>

        <p class="bottom-text">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('roleSelect');
    const seekerFields = document.querySelectorAll('.seeker-only');
    
    const careerInput = document.getElementById('careerInput');
    const skillsInput = document.getElementById('skillsInput');
    const locationInput = document.getElementById('locationInput');
    const cvInput = document.getElementById('cvInput');

    function toggleRoleFields() {
        if (roleSelect.value === 'recruiter') {
            seekerFields.forEach(field => field.style.display = 'none');
            
            careerInput.removeAttribute('required');
            skillsInput.removeAttribute('required');
            locationInput.removeAttribute('required');
            cvInput.removeAttribute('required');
        } else {

        seekerFields.forEach(field => field.style.display = 'block');
            

        careerInput.setAttribute('required', '');
            skillsInput.setAttribute('required', '');
            locationInput.setAttribute('required', '');
            cvInput.setAttribute('required', '');
        }
    }

    roleSelect.addEventListener('change', toggleRoleFields);
    toggleRoleFields(); 
});
</script>

</body>
</html>