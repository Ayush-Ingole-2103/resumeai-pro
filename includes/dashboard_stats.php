<?php

$user_id = $_SESSION['user_id'];

/* Total Resumes */
$stmt = $conn->prepare("
SELECT COUNT(*) AS total
FROM resumes
WHERE user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();
$totalResumes = $stmt->get_result()->fetch_assoc()['total'];

/* Average ATS */
$stmt = $conn->prepare("
SELECT AVG(ats_score) AS avg_score
FROM resumes
WHERE user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$avgATS = $stmt->get_result()->fetch_assoc()['avg_score'];

if ($avgATS === null) {
    $avgATS = 0;
}

/* Total Analyzed */
$stmt = $conn->prepare("
SELECT COUNT(*) AS analyzed
FROM resumes
WHERE user_id=?
AND status='Analyzed'
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$analyzed = $stmt->get_result()->fetch_assoc()['analyzed'];

/* Recent Uploads */
$stmt = $conn->prepare("
SELECT resume_title, resume_type, status, upload_date
FROM resumes
WHERE user_id=?
ORDER BY upload_date DESC
LIMIT 5
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$recentUploads = $stmt->get_result();
?>