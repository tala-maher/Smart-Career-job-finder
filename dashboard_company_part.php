<?php
$rec_sql = "SELECT a.id AS app_id, u.full_name, u.email, u.cv_path, j.title AS job_title, 
                   a.match_score, a.match_explanation, a.missing_skills
            FROM applications a
            JOIN users u ON a.user_id = u.id
            JOIN jobs j ON a.job_id = j.id
            WHERE j.company_id = :company_id
            ORDER BY a.match_score DESC";
$rec_stmt = $conn->prepare($rec_sql);
$rec_stmt->execute(['company_id' => $user_id]);
$applications = $rec_stmt->fetchAll();
?>

<h3 style="margin-bottom: 15px;"><i class="fa-solid fa-layer-group"></i> Incoming Candidate Applications (AI Sorted)</h3>

<?php if (count($applications) > 0): ?>
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <?php foreach ($applications as $app): ?>
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #10b981;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                    <div>
                        <h4 style="margin: 0; font-size: 1.2rem;"><?php echo htmlspecialchars($app['full_name']); ?></h4>
                        <p style="margin: 2px 0; color: #555;">Applied for: <strong><?php echo htmlspecialchars($app['job_title']); ?></strong></p>
                        <p style="margin: 2px 0; font-size: 0.9rem; color: #777;"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($app['email']); ?></p>
                    </div>
                    <span style="background: #e6f4ea; color: #137333; padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 0.9rem;">
                        <?php echo $app['match_score']; ?>% Match
                    </span>
                </div>
                
                <div style="background: #f9f9f9; padding: 12px; border-radius: 6px; margin-top: 10px; font-size: 0.95rem;">
                    <p style="margin: 0 0 5px 0;"><strong><i class="fa-solid fa-brain" style="color: #2b70c9;"></i> AI Assessment:</strong> <?php echo htmlspecialchars($app['match_explanation']); ?></p>
                    <?php if (!empty($app['missing_skills'])): ?>
                        <p style="margin: 5px 0 0 0; color: #b91c1c;"><strong><i class="fa-solid fa-triangle-exclamation"></i> Skill Gaps:</strong> <?php echo htmlspecialchars($app['missing_skills']); ?></p>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 15px;">
                    <?php if (!empty($app['cv_path'])): ?>
                        <a href="<?php echo htmlspecialchars($app['cv_path']); ?>" target="_blank" style="color: #2b70c9; text-decoration: none; font-weight: bold; font-size: 0.9rem;"><i class="fa-solid fa-file-pdf"></i> View Applicant CV</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div style="background: white; padding: 30px; text-align: center; border-radius: 8px; color: #777;">
        <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
        <p>No candidate applications found for your hosted campaigns yet.</p>
    </div>
<?php endif; ?>