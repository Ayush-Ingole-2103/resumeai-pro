<?php

/*=====================================================
    REPORT PAGE
    AI Resume Analyzer & ATS Checker
======================================================*/

session_start();

include_once "../includes/dashboard_header.php";
include_once "../config/db.php";

/*=====================================================
    Validate Request
======================================================*/

if (!isset($_SESSION['user_id'])) {

    header("Location: ../auth/login.php");
    exit();

}

if (!isset($_GET['id'])) {

    die("Resume ID Missing.");

}

$resume_id = intval($_GET['id']);
$user_id   = $_SESSION['user_id'];

/*=====================================================
    Fetch Resume
======================================================*/

$stmt = $conn->prepare("
SELECT *
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

/*=====================================================
    Fetch Analysis
======================================================*/

$stmt = $conn->prepare("
SELECT *
FROM analysis
WHERE resume_id=?
LIMIT 1
");

$stmt->bind_param("i",$resume_id);
$stmt->execute();

$analysis = $stmt->get_result()->fetch_assoc();

if(!$analysis){

    die("Analysis not found.");

}

/*=====================================================
    Fetch Skills
======================================================*/

$stmt = $conn->prepare("
SELECT skills.skill_name

FROM resume_skills

INNER JOIN skills

ON resume_skills.skill_id = skills.skill_id

WHERE resume_skills.resume_id=?

ORDER BY skills.skill_name
");

$stmt->bind_param("i",$resume_id);
$stmt->execute();

$skills = $stmt->get_result();

/*=====================================================
    Variables
======================================================*/

$atsScore = intval($analysis['ats_score']);

$completenessScore = intval($analysis['completeness_score']);

$completenessReport = explode(
    "\n",
    $analysis['completeness_report']
);

$strengths = array_filter(
    array_map(
        "trim",
        preg_split('/[\r\n\.]+/', $analysis['strengths'])
    )
);

$weaknesses = array_filter(
    array_map(
        "trim",
        preg_split('/[\r\n\.]+/', $analysis['weaknesses'])
    )
);

$suggestions = array_filter(
    array_map(
        "trim",
        preg_split('/[\r\n\.]+/', $analysis['suggestions'])
    )
);

$analysisWords = str_word_count(
    $analysis['strengths'] .
    $analysis['weaknesses'] .
    $analysis['suggestions']
);

$modulesCompleted = 0;

if(!empty($analysis['strengths'])) $modulesCompleted++;
if(!empty($analysis['weaknesses'])) $modulesCompleted++;
if(!empty($analysis['suggestions'])) $modulesCompleted++;

?>
<div class="content-wrapper">

<section class="content-header">

<div class="container-fluid">

<h1>

Resume ATS Report

</h1>

</div>

</section>

<section class="content">

<div class="container-fluid">

<!-- =====================================================
     STATISTICS CARDS
====================================================== -->

<div class="row">

<div class="col-lg-3 col-md-6">

<div class="small-box bg-info">

<div class="inner">

<h3><?= $analysisWords ?></h3>

<p>Analysis Words</p>

</div>

<div class="icon">

<i class="fas fa-file-word"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="small-box bg-success">

<div class="inner">

<h3><?= $skills->num_rows ?></h3>

<p>Detected Skills</p>

</div>

<div class="icon">

<i class="fas fa-code"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="small-box bg-warning">

<div class="inner">

<h3><?= $modulesCompleted ?>/3</h3>

<p>Analysis Modules</p>

</div>

<div class="icon">

<i class="fas fa-layer-group"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="small-box bg-danger">

<div class="inner">

<h3><?= $atsScore ?>%</h3>

<p>ATS Score</p>

</div>

<div class="icon">

<i class="fas fa-chart-line"></i>

</div>

</div>

</div>

</div>

<!-- =====================================================
     MAIN CONTENT STARTS HERE
====================================================== -->

<div class="row">

<!-- =====================================================
     ATS SCORE CARD
====================================================== -->

<div class="col-lg-4">

<div class="card card-primary card-outline">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-chart-line mr-2"></i>

ATS Score

</h3>

</div>

<div class="card-body text-center">

<?php

if($atsScore >= 80){

    $badgeColor = "success";
    $statusText = "Excellent";

}
elseif($atsScore >= 60){

    $badgeColor = "warning";
    $statusText = "Good";

}
else{

    $badgeColor = "danger";
    $statusText = "Needs Improvement";

}

?>

<div
style="
width:180px;
height:180px;
border-radius:50%;
margin:auto;
border:12px solid #28a745;
display:flex;
justify-content:center;
align-items:center;
flex-direction:column;
">

<h1 class="display-3 mb-0">

<?= $atsScore ?>

</h1>

<h4>%</h4>

</div>

<br>

<span class="badge badge-<?= $badgeColor ?> p-2">

<?= $statusText ?>

</span>

<hr>

<div class="progress">

<div
class="progress-bar bg-success"
style="width:<?= $atsScore ?>%;">

</div>

</div>

<br>

<p class="text-muted">

Overall ATS Compatibility

</p>

</div>

</div>

</div>

<!-- =====================================================
     RESUME COMPLETENESS
====================================================== -->

<div class="col-lg-4">

<div class="card card-info card-outline">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-clipboard-check mr-2"></i>

Resume Completeness

</h3>

</div>

<div class="card-body">

<div class="text-center">

<h1 class="display-4 text-info">

<?= $completenessScore ?>%

</h1>

<p>

Completeness Score

</p>

</div>

<div class="progress mb-3">

<div
class="progress-bar bg-info"
style="width:<?= $completenessScore ?>%;">

</div>

</div>

<ul class="list-group">

<?php

foreach($completenessReport as $item){

if(trim($item)=="") continue;

$success = strpos($item,"✓")!==false;

?>

<li class="list-group-item">

<span class="<?= $success ? 'text-success' : 'text-danger' ?>">

<?= htmlspecialchars($item) ?>

</span>

</li>

<?php } ?>

</ul>

</div>

</div>

</div>

<!-- =====================================================
     RESUME SUMMARY
====================================================== -->

<div class="col-lg-4">

<div class="card card-success card-outline">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-file-alt mr-2"></i>

Resume Summary

</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th width="40%">

Title

</th>

<td>

<?= htmlspecialchars($resume['resume_title']) ?>

</td>

</tr>

<tr>

<th>

Type

</th>

<td>

<?= htmlspecialchars($resume['resume_type']) ?>

</td>

</tr>

<tr>

<th>

Status

</th>

<td>

<span class="badge badge-success">

<?= htmlspecialchars($resume['status']) ?>

</span>

</td>

</tr>

<tr>

<th>

Uploaded

</th>

<td>

<?= date("d M Y",strtotime($resume['upload_date'])) ?>

</td>

</tr>

<tr>

<th>

Analyzed

</th>

<td>

<?= date("d M Y h:i A",strtotime($analysis['analyzed_at'])) ?>

</td>

</tr>

<tr>

<th>

ATS Score

</th>

<td>

<strong>

<?= $atsScore ?>%

</strong>

</td>

</tr>

</table>

</div>

</div>

</div>

</div>

<!-- =====================================================
     DETECTED SKILLS STARTS BELOW
====================================================== -->

<!-- =====================================================
     DETECTED SKILLS
====================================================== -->

<div class="card card-primary card-outline mt-4">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-code mr-2"></i>

Detected Skills

</h3>

</div>

<div class="card-body">

<?php

if($skills->num_rows==0){

?>

<div class="alert alert-warning">

<i class="fas fa-exclamation-triangle"></i>

No technical skills were detected in this resume.

</div>

<?php

}else{

while($skill = $skills->fetch_assoc()){

?>

<span
class="badge badge-primary p-2 m-1"
style="font-size:15px;">

<?= htmlspecialchars($skill['skill_name']) ?>

</span>

<?php

}

}

?>

</div>

</div>

<!-- =====================================================
     STRENGTHS
====================================================== -->

<div class="card card-success card-outline mt-4">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-check-circle mr-2"></i>

Strengths

</h3>

</div>

<div class="card-body">

<?php

if(count($strengths)==0){

?>

<div class="alert alert-secondary">

No strengths detected.

</div>

<?php

}else{

foreach($strengths as $item){

?>

<div class="alert alert-success">

<i class="fas fa-check-circle mr-2"></i>

<?= htmlspecialchars($item) ?>

</div>

<?php

}

}

?>

</div>

</div>

<!-- =====================================================
     WEAKNESSES
====================================================== -->

<div class="card card-danger card-outline mt-4">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-times-circle mr-2"></i>

Weaknesses

</h3>

</div>

<div class="card-body">

<?php

if(count($weaknesses)==0){

?>

<div class="alert alert-success">

Excellent!

No weaknesses detected.

</div>

<?php

}else{

foreach($weaknesses as $item){

?>

<div class="alert alert-danger">

<i class="fas fa-times-circle mr-2"></i>

<?= htmlspecialchars($item) ?>

</div>

<?php

}

}

?>

</div>

</div>

<!-- =====================================================
     SUGGESTIONS
====================================================== -->

<div class="card card-warning card-outline mt-4">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-lightbulb mr-2"></i>

Suggestions for Improvement

</h3>

</div>

<div class="card-body">

<?php

if(count($suggestions)==0){

?>

<div class="alert alert-info">

No suggestions available.

</div>

<?php

}else{

foreach($suggestions as $item){

?>

<div class="alert alert-warning">

<i class="fas fa-arrow-circle-right mr-2"></i>

<?= htmlspecialchars($item) ?>

</div>

<?php

}

}

?>

</div>

</div>

<!-- =====================================================
     ATS CHART + REPORT FOOTER STARTS BELOW
====================================================== -->

<!-- =====================================================
     ATS SCORE CHART
====================================================== -->

<div class="card card-dark card-outline mt-4">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-chart-bar mr-2"></i>

ATS Score Visualization

</h3>

</div>

<div class="card-body">

<canvas id="atsChart" height="100"></canvas>

</div>

</div>

<!-- =====================================================
     QUICK STATS
====================================================== -->

<hr>

<div class="row text-center mt-4">

<div class="col-md-3">

<h3 class="text-primary">

<?= $atsScore ?>%

</h3>

<p>ATS Score</p>

</div>

<div class="col-md-3">

<h3 class="text-info">

<?= $completenessScore ?>%

</h3>

<p>Completeness</p>

</div>

<div class="col-md-3">

<h3 class="text-success">

<?= $skills->num_rows ?>

</h3>

<p>Skills</p>

</div>

<div class="col-md-3">

<h3 class="text-warning">

<?= date("Y") ?>

</h3>

<p>Analysis Year</p>

</div>

</div>

<!-- =====================================================
     REPORT FOOTER
====================================================== -->

<hr>

<div class="text-center text-muted mt-4 mb-4">

<h5>

AI Resume Analyzer & ATS Checker

</h5>

<p>

Generated on

<strong>

<?= date("d M Y h:i A") ?>

</strong>

</p>

<p>

Professional Resume Analysis Report

</p>

</div>

<!-- =====================================================
     ACTION BUTTONS
====================================================== -->

<div class="text-center mt-5 mb-5">

<a
href="../dashboard/history.php"
class="btn btn-primary">

<i class="fas fa-arrow-left"></i>

Back

</a>

<button
class="btn btn-success"
onclick="window.print()">

<i class="fas fa-print"></i>

Print Report

</button>

<a
href="../dashboard/upload.php"
class="btn btn-info">

<i class="fas fa-upload"></i>

Analyze Another

</a>

<a
href="../dashboard/index.php"
class="btn btn-dark">

<i class="fas fa-home"></i>

Dashboard

</a>

</div>

</div>

</section>

</div>

<!-- =====================================================
     CHART JS
====================================================== -->

<script src="../plugins/chart.js/Chart.min.js"></script>

<script>

new Chart(document.getElementById("atsChart"),{

    type:'bar',

    data:{

        labels:[
            'ATS Score',
            'Completeness'
        ],

        datasets:[{

            label:'Score',

            data:[
                <?= $atsScore ?>,
                <?= $completenessScore ?>
            ]

        }]

    },

    options:{

        responsive:true,

        plugins:{

            legend:{
                display:false
            }

        },

        scales:{

            y:{

                beginAtZero:true,

                max:100

            }

        }

    }

});

</script>

<?php

include_once "../includes/dashboard_footer.php";

?>