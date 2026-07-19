<?php

/*=========================================================
    AI Resume Analyzer & ATS Checker
    File : analysis/analyze.php
==========================================================*/

session_start();

/*=========================================================
    Required Files
==========================================================*/

require_once "../config/db.php";

require_once "../engine/ResumeAnalyzer.php";
require_once "../engine/TextFormatter.php";
require_once "../engine/ATSScorer.php";
require_once "../engine/SkillExtractor.php";
require_once "../engine/SuggestionEngine.php";
require_once "../engine/ResumeCompleteness.php";

require_once "SectionDetector.php";

/*=========================================================
    Validate User Session
==========================================================*/

if (!isset($_SESSION['user_id'])) {

    header("Location: ../auth/login.php");
    exit();

}

/*=========================================================
    Validate Resume ID
==========================================================*/

if (!isset($_GET['id'])) {

    die("Resume ID Missing.");

}

$resume_id = intval($_GET['id']);
$user_id   = $_SESSION['user_id'];

/*=========================================================
    Fetch Resume
==========================================================*/

$stmt = $conn->prepare("
SELECT *
FROM resumes
WHERE resume_id = ?
AND user_id = ?
LIMIT 1
");

if(!$stmt){

    die($conn->error);

}

$stmt->bind_param("ii",$resume_id,$user_id);

$stmt->execute();

$resume = $stmt->get_result()->fetch_assoc();

$stmt->close();

if(!$resume){

    die("Resume not found or access denied.");

}

/*=========================================================
    Start Transaction
==========================================================*/

$conn->begin_transaction();

try{

/*=========================================================
    Resume Text Extraction
==========================================================*/

$resumeEngine = new ResumeAnalyzer($conn);

$text = $resumeEngine->getResumeText($resume);

if(trim($text)==""){

    throw new Exception("Unable to extract resume text.");

}

/*=========================================================
    Detect Resume Sections
==========================================================*/

$sections = SectionDetector::detect($text);

/*=========================================================
    ATS Score Calculation
==========================================================*/

$atsEngine = new ATSScorer(
    $sections,
    $text
);

$atsResult = $atsEngine->calculate();

$score  = intval($atsResult['score']);
$report = $atsResult['report'];

/*=========================================================
    Extract Technical Skills
==========================================================*/

$skillEngine = new SkillExtractor($text);

$skills = $skillEngine->extract();

/*=========================================================
    Generate Suggestions
==========================================================*/

$suggestionEngine = new SuggestionEngine(
    $sections,
    $skills,
    $text
);

$feedback = $suggestionEngine->generate();

$strengths  = $feedback['strengths'];
$weaknesses = $feedback['weaknesses'];
$suggestions = $feedback['suggestions'];

/*=========================================================
    Resume Completeness
==========================================================*/

$completeEngine = new ResumeCompleteness(
    $sections,
    $text
);

$completeResult = $completeEngine->calculate();

$completenessScore = intval(
    $completeResult['score']
);

$completenessReport = implode(
    "\n",
    $completeResult['report']
);

/*=========================================================
    DATABASE OPERATIONS START BELOW
==========================================================*/

/*=========================================================
    Update Resume Status
==========================================================*/

$stmt = $conn->prepare("
UPDATE resumes
SET
    ats_score = ?,
    status = 'Analyzed',
    analyzed_at = NOW()
WHERE resume_id = ?
");

if(!$stmt){

    throw new Exception($conn->error);

}

$stmt->bind_param(
    "ii",
    $score,
    $resume_id
);

if(!$stmt->execute()){

    throw new Exception($stmt->error);

}

$stmt->close();


/*=========================================================
    Remove Previous Analysis
==========================================================*/

$stmt = $conn->prepare("
DELETE FROM analysis
WHERE resume_id = ?
");

if(!$stmt){

    throw new Exception($conn->error);

}

$stmt->bind_param(
    "i",
    $resume_id
);

if(!$stmt->execute()){

    throw new Exception($stmt->error);

}

$stmt->close();


/*=========================================================
    Insert New Analysis
==========================================================*/

$stmt = $conn->prepare("
INSERT INTO analysis
(
    resume_id,
    ats_score,
    completeness_score,
    strengths,
    weaknesses,
    suggestions,
    completeness_report
)
VALUES
(
    ?, ?, ?, ?, ?, ?, ?
)
");

if(!$stmt){

    throw new Exception($conn->error);

}

$stmt->bind_param(
    "iiissss",
    $resume_id,
    $score,
    $completenessScore,
    $strengths,
    $weaknesses,
    $suggestions,
    $completenessReport
);

if(!$stmt->execute()){

    throw new Exception($stmt->error);

}

$stmt->close();


/*=========================================================
    Remove Previously Saved Skills
==========================================================*/

$stmt = $conn->prepare("
DELETE FROM resume_skills
WHERE resume_id = ?
");

if(!$stmt){

    throw new Exception($conn->error);

}

$stmt->bind_param(
    "i",
    $resume_id
);

if(!$stmt->execute()){

    throw new Exception($stmt->error);

}

$stmt->close();


/*=========================================================
    Save Extracted Skills
==========================================================*/

foreach($skills as $skill){

    $stmt = $conn->prepare("
    SELECT skill_id
    FROM skills
    WHERE skill_name = ?
    LIMIT 1
    ");

    if(!$stmt){

        throw new Exception($conn->error);

    }

    $stmt->bind_param(
        "s",
        $skill
    );

    if(!$stmt->execute()){

        throw new Exception($stmt->error);

    }

    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $row = $result->fetch_assoc();

        $skill_id = $row['skill_id'];

        $insert = $conn->prepare("
        INSERT INTO resume_skills
        (
            resume_id,
            skill_id
        )
        VALUES
        (
            ?, ?
        )
        ");

        if(!$insert){

            throw new Exception($conn->error);

        }

        $insert->bind_param(
            "ii",
            $resume_id,
            $skill_id
        );

        if(!$insert->execute()){

            throw new Exception($insert->error);

        }

        $insert->close();

    }

    $stmt->close();

}

/*=========================================================
    Finish Transaction
==========================================================*/

/*=========================================================
    Commit Transaction
==========================================================*/

$conn->commit();

/*=========================================================
    Redirect to Report
==========================================================*/

header("Location: report.php?id=".$resume_id);

exit();

}

/*=========================================================
    Error Handling
==========================================================*/

catch(Exception $e){

    $conn->rollback();

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Analysis Failed</title>

<link rel="stylesheet"
href="../plugins/bootstrap/css/bootstrap.min.css">

<link rel="stylesheet"
href="../plugins/fontawesome-free/css/all.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow">

<div class="card-header bg-danger text-white">

<h4 class="mb-0">

<i class="fas fa-times-circle"></i>

Analysis Failed

</h4>

</div>

<div class="card-body">

<div class="alert alert-danger">

Something went wrong while analyzing the resume.

</div>

<h5>Error Details</h5>

<pre><?= htmlspecialchars($e->getMessage()) ?></pre>

<hr>

<div class="text-center">

<a
href="../dashboard/history.php"
class="btn btn-primary">

<i class="fas fa-arrow-left"></i>

Back to Resume History

</a>

<a
href="../dashboard/index.php"
class="btn btn-dark">

<i class="fas fa-home"></i>

Dashboard

</a>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>

<?php

}