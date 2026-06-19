<?php
session_start();

// Include the environment loader securely
include "env_loader.php";

$host     = getenv('DB_HOST') ?: "localhost";
$dbname   = getenv('DB_NAME') ?: "smart_career_db";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $e) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed."
    ]));
}

/**
 * Calculate the match score between a candidate and a job,
 * generate recommendations, and store the results.
 */
function processApplicationMatching($conn, $application_id)
{
    try {
        // Retrieve application, candidate, and job data
        $query = "SELECT a.id AS app_id, u.skills AS user_skills, j.required_skills AS job_skills
                  FROM applications a
                  JOIN users u ON a.user_id = u.id
                  JOIN jobs j ON a.job_id = j.id
                  WHERE a.id = :app_id";

        $stmt = $conn->prepare($query);
        $stmt->execute(['app_id' => $application_id]);
        $data = $stmt->fetch();

        if (!$data) {
            return false;
        }

        // Normalize skills for comparison
        $user_skills_raw = strtolower($data['user_skills']);
        $job_skills_raw  = strtolower($data['job_skills']);

        $user_skills_arr = array_map('trim', explode(',', $user_skills_raw));
        $job_skills_arr  = array_map('trim', explode(',', $job_skills_raw));

        // Calculate matched and missing skills
        $matched_skills = array_intersect($job_skills_arr, $user_skills_arr);
        $missing_skills = array_diff($job_skills_arr, $user_skills_arr);

        // Calculate match percentage
        $total_required_count = count($job_skills_arr);
        $matched_count        = count($matched_skills);

        $match_score = $total_required_count > 0 ? round(($matched_count / $total_required_count) * 100) : 0;
        $missing_skills_str = implode(', ', $missing_skills);

        // Default matching summary
        $match_explanation = "Candidate matched {$matched_count} out of {$total_required_count} required skills.";
        $recommended_improvements = "Improve the following skills: {$missing_skills_str}";

        //  Fetch the OpenAI API Key from environment variables safely
        $api_key = getenv('OPENAI_API_KEY');

        if (!empty($api_key) && !empty($missing_skills)) {

            $prompt = "Job requires: [{$job_skills_raw}]. " .
                      "Candidate skills: [{$user_skills_raw}]. " .
                      "Missing skills: [{$missing_skills_str}]. " .
                      "Provide a short match explanation and learning recommendations.";

            $payload = [
                "model" => "gpt-4o-mini", 
                "messages" => [
                    ["role" => "system", "content" => "You are an AI recruitment assistant."],
                    ["role" => "user", "content" => $prompt]
                ],
                "temperature" => 0.3
            ];

            $ch = curl_init("https://api.openai.com/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer " . $api_key
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $res_data = json_decode($response, true);
                if (isset($res_data['choices'][0]['message']['content'])) {
                    $match_explanation = $res_data['choices'][0]['message']['content'];
                }
            }
        }

        // Save matching results 
        $update_sql = "UPDATE applications
                       SET match_score = :match_score,
                           match_explanation = :match_explanation,
                           missing_skills = :missing_skills,
                           recommended_improvements = :recommended_improvements
                       WHERE id = :app_id";

        $update_stmt = $conn->prepare($update_sql);

        return $update_stmt->execute([
            'match_score'              => $match_score,
            'match_explanation'        => $match_explanation,
            'missing_skills'           => $missing_skills_str,
            'recommended_improvements' => $recommended_improvements,
            'app_id'                   => $application_id
        ]);

    } catch (PDOException $e) {
        return false;
    }
}

// Handle matching requests
if (isset($_GET['trigger_app_id'])) {
    $target_id = (int) $_GET['trigger_app_id'];
    $execution_result = processApplicationMatching($conn, $target_id);

    if ($execution_result) {
        echo json_encode([
            "status" => "success",
            "message" => "Matching process completed successfully."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Matching process failed."
        ]);
    }
}
?>