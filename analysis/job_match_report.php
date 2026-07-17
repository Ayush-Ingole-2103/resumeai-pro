<?php

session_start();

require_once "../config/db.php";
require_once "../engine/JobDescriptionParser.php";
require_once "../engine/JobMatcher.php";
require_once "../engine/JobMetadataParser.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

if(
    !isset($_POST['resume_id']) ||
    !isset($_POST['job_description'])
){
    die("Invalid Request.");
}

$resume_id = intval($_POST['resume_id']);
$user_id = $_SESSION['user_id'];
$jobDescription = trim($_POST['job_description']);

/* ===========================================
   Verify Resume Ownership
=========================================== */

$stmt = $conn->prepare("
SELECT resume_id,resume_title
FROM resumes
WHERE resume_id=?
AND user_id=?
LIMIT 1
");

$stmt->bind_param("ii",$resume_id,$user_id);
$stmt->execute();

$resume = $stmt->get_result()->fetch_assoc();

if(!$resume){
    die("Resume not found.");
}

/* ===========================================
   Fetch Resume Skills
=========================================== */

$stmt = $conn->prepare("
SELECT skills.skill_name

FROM resume_skills

INNER JOIN skills
ON skills.skill_id = resume_skills.skill_id

WHERE resume_skills.resume_id=?
");

$stmt->bind_param("i",$resume_id);
$stmt->execute();

$result = $stmt->get_result();

$resumeSkills = [];

while($row = $result->fetch_assoc()){

    $resumeSkills[] = $row['skill_name'];

}

/* ===========================================
   Parse Job Description
=========================================== */

$parser = new JobDescriptionParser(
    $conn,
    $jobDescription
);

$jobSkills = $parser->extractSkills();


/* ===========================================
   Job Metadata
=========================================== */

$metadataParser = new JobMetadataParser($jobDescription);

$metadata = $metadataParser->parse();


/* ===========================================
   Match Resume
=========================================== */

$matcher = new JobMatcher(
    $resumeSkills,
    $jobSkills
);

$match = $matcher->match();

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Job Match Report</title>

<link rel="stylesheet"
href="../plugins/bootstrap/css/bootstrap.min.css">

<link rel="stylesheet"
href="../plugins/fontawesome-free/css/all.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row">

<div class="col-lg-12">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>

Resume Job Match Report

</h3>

</div>

<div class="card-body">

<h4>

Resume :

<?= htmlspecialchars($resume['resume_title']) ?>

</h4>

<hr>

<div class="row">

<div class="col-md-4">

<div class="alert alert-<?= $match['color'] ?> text-center">

<h1>

<?= $match['score'] ?>%

</h1>

<strong>

Match Score

</strong>

</div>

</div>

<div class="col-md-8">

<div class="alert alert-<?= $match['color'] ?>">

<h4>

<?= $match['verdict'] ?>

</h4>

<p>

Matched

<?= $match['matched_count'] ?>

of

<?= $match['required_count'] ?>

required skills.

<br>
<small>
Weighted Score:
<?= $match['matched_weight'] ?> / <?= $match['total_weight'] ?>
</small>

</p>

</div>

</div>

</div>

<hr>

<div class="card mb-4">

<div class="card-header bg-dark text-white">

<h5 class="mb-0">

Job Information

</h5>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4">

<p>

<strong>Experience</strong>

<br>

<?= htmlspecialchars($metadata['experience']) ?>

</p>

</div>

<div class="col-md-4">

<p>

<strong>Job Level</strong>

<br>

<?= htmlspecialchars($metadata['level']) ?>

</p>

</div>

<div class="col-md-4">

<p>

<strong>Employment</strong>

<br>

<?= htmlspecialchars($metadata['employment']) ?>

</p>

</div>

</div>

<div class="row mt-3">

<div class="col-md-6">

<p>

<strong>Work Mode</strong>

<br>

<?= htmlspecialchars($metadata['mode']) ?>

</p>

</div>

<div class="col-md-6">

<p>

<strong>Education</strong>

<br>

<?= htmlspecialchars($metadata['education']) ?>

</p>

</div>

</div>

</div>

</div>

<hr>

<div class="row">

<div class="col-md-4">

<div class="card border-success">

<div class="card-header bg-success text-white">

Matched Skills

</div>

<div class="card-body">

<?php

if(count($match['matched_skills'])==0){

echo "<p>No matched skills.</p>";

}else{

foreach($match['matched_skills'] as $skill){

echo "<span class='badge badge-success mr-1 mb-2'>";

echo htmlspecialchars($skill);

echo "</span>";

}

}

?>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card border-danger">

<div class="card-header bg-danger text-white">

Missing Skills

</div>

<div class="card-body">

<?php

if(count($match['missing_skills'])==0){

echo "<p>No missing skills.</p>";

}else{

foreach($match['missing_skills'] as $skill){

echo "<span class='badge badge-danger mr-1 mb-2'>";

echo htmlspecialchars($skill);

echo "</span>";

}

}

?>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card border-primary">

<div class="card-header bg-primary text-white">

Extra Skills

</div>

<div class="card-body">

<?php

if(count($match['extra_skills'])==0){

echo "<p>No extra skills.</p>";

}else{

foreach($match['extra_skills'] as $skill){

echo "<span class='badge badge-primary mr-1 mb-2'>";

echo htmlspecialchars($skill);

echo "</span>";

}

}

?>

</div>

</div>

</div>

</div>

<hr>

<div class="card">

<div class="card-header bg-warning">

Recommendations

</div>

<div class="card-body">

<?php

if(count($match['missing_skills'])==0){

echo "<div class='alert alert-success'>";

echo "Excellent! Your resume matches all detected job skills.";

echo "</div>";

}else{

echo "<ul>";

foreach($match['missing_skills'] as $skill){

echo "<li>";

echo "Consider adding experience with <strong>";

echo htmlspecialchars($skill);

echo "</strong> if applicable.";

echo "</li>";

}

echo "</ul>";

}

?>

</div>

</div>

<div class="mt-4">

<a href="../dashboard/job_matcher.php"
class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>

Back

</a>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>