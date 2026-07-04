<?php

require_once "../config/db.php";
require_once "../engine/ResumeAnalyzer.php";
require_once "SectionDetector.php";

if (!isset($_GET['id'])) {
    die("Resume ID Missing");
}

$resume_id = intval($_GET['id']);

$engine = new ResumeAnalyzer($conn);

// Fetch resume details
$resume = $engine->getResume($resume_id);

if (!$resume) {
    die("Resume not found.");
}

// Extract text from resume
$text = $engine->getResumeText($resume);

// Detect sections
$sections = SectionDetector::detect($text);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Resume Analysis</title>

    <link rel="stylesheet"
          href="../plugins/bootstrap/css/bootstrap.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

    <h2 class="mb-4">Resume Section Detection</h2>

    <?php foreach($sections as $section => $content){ ?>

        <div class="card mb-4">

            <div class="card-header bg-primary text-white">

                <strong><?= ucfirst($section) ?></strong>

            </div>

            <div class="card-body">

                <?php
                if(trim($content)==""){
                    echo "<span class='text-muted'>Not Found</span>";
                }else{
                    echo "<pre style='white-space:pre-wrap;font-family:inherit;'>";
                    echo htmlspecialchars($content);
                    echo "</pre>";
                }
                ?>

            </div>

        </div>

    <?php } ?>

</div>

</body>

</html>