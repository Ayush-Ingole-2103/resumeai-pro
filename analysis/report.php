<?php

include_once "../includes/dashboard_header.php";
include_once "../config/db.php";

if(!isset($_GET['id'])){
    die("Resume ID Missing.");
}

$resume_id = intval($_GET['id']);

/* ---------------------------------
   Resume Details
----------------------------------*/

$stmt = $conn->prepare("
SELECT *
FROM resumes
WHERE resume_id=?
");

$stmt->bind_param("i",$resume_id);
$stmt->execute();

$resume = $stmt->get_result()->fetch_assoc();

if(!$resume){
    die("Resume not found.");
}

/* ---------------------------------
   Analysis Report
----------------------------------*/

$stmt = $conn->prepare("
SELECT *
FROM analysis
WHERE resume_id=?
");

$stmt->bind_param("i",$resume_id);
$stmt->execute();

$analysis = $stmt->get_result()->fetch_assoc();

if(!$analysis){
    die("Analysis not found.");
}

/* ---------------------------------
   Skills
----------------------------------*/

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

$score = intval($analysis['ats_score']);
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

<!-- Statistics Cards -->

<div class="row">

<div class="col-lg-3 col-6">

<div class="small-box bg-info">

<div class="inner">

<?php

$wordCount = str_word_count(
    $analysis['strengths'].
    $analysis['weaknesses'].
    $analysis['suggestions']
);

?>

<h3><?= $wordCount ?></h3>

<p>Analysis Words</p>

</div>

<div class="icon">

<i class="fas fa-file-word"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-6">

<div class="small-box bg-success">

<div class="inner">

<h3>

<?= $skills->num_rows ?>

</h3>

<p>

Skills Found

</p>

</div>

<div class="icon">

<i class="fas fa-code"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-6">

<div class="small-box bg-warning">

<div class="inner">

<?php

$sectionsCompleted = 0;

if(!empty($analysis['strengths'])) $sectionsCompleted++;
if(!empty($analysis['weaknesses'])) $sectionsCompleted++;
if(!empty($analysis['suggestions'])) $sectionsCompleted++;

?>

<h3>

<?= $sectionsCompleted ?>/3

</h3>

<p>

Analysis Modules

</p>

</div>

<div class="icon">

<i class="fas fa-list"></i>

</div>

</div>

</div>

<div class="col-lg-3 col-6">

<div class="small-box bg-danger">

<div class="inner">

<h3>

<?= $score ?>%

</h3>

<p>

ATS Rating

</p>

</div>

<div class="icon">

<i class="fas fa-chart-line"></i>

</div>

</div>

</div>

</div>

<div class="row">

<div class="col-md-4">

<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">

ATS Score

</h3>

</div>

<div class="card-body text-center">

<?php

if($score>=80){

    $color="success";
    $text="Excellent";

}
elseif($score>=60){

    $color="warning";
    $text="Good";

}
else{

    $color="danger";
    $text="Needs Improvement";

}

?>

<div class="text-center">

<div
style="width:180px;
height:180px;
border-radius:50%;
margin:auto;
border:12px solid #28a745;
display:flex;
align-items:center;
justify-content:center;
flex-direction:column;">

<h1 class="display-4">

<?= $score ?>

</h1>

<h4>%</h4>

</div>

</div>

<h4>

<?= $text ?>

</h4>



<hr>

<div class="progress">

<div
class="progress-bar bg-success"
style="width:<?= $score ?>%;">

</div>

</div>

<br>

<small class="text-muted">

Resume Quality Meter

</small>

<p>

Resume:

<strong>

<?= htmlspecialchars($resume['resume_title']) ?>

</strong>

</p>

<p>

Type:

<?= htmlspecialchars($resume['resume_type']) ?>

</p>

<p>

Analyzed On:

<br>

<?= date("d M Y h:i A",strtotime($analysis['analyzed_at'])) ?>

</p>

</div>

</div>

</div>

<div class="col-md-8">

<div class="card">

<div class="card-header bg-success">

<h3 class="card-title">

Analysis Summary

</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th width="35%">Resume Title</th>

<td><?= htmlspecialchars($resume['resume_title']) ?></td>

</tr>

<tr>

<th>Resume Type</th>

<td><?= htmlspecialchars($resume['resume_type']) ?></td>

</tr>

<tr>

<th>Status</th>

<td>

<span class="badge badge-success">

<?= $resume['status'] ?>

</span>

</td>

</tr>

<tr>

<th>ATS Score</th>

<td>

<strong>

<?= $score ?>%

</strong>

</td>

</tr>

<tr>

<th>Uploaded</th>

<td>

<?= date("d M Y",strtotime($resume['upload_date'])) ?>

</td>

</tr>

</table>

</div>

</div>

</div>

</div>

<div class="card mt-4">

<div class="card-header bg-info">

<h3 class="card-title">

Detected Skills

</h3>

</div>

<div class="card-body">

<?php

if($skills->num_rows==0){

?>

<div class="alert alert-warning">

<i class="fas fa-exclamation-circle"></i>

No recognizable technical skills were found in this resume.

</div>

<?php

}else{

while($skill=$skills->fetch_assoc()){

?>

<span
class="badge badge-primary p-2 m-1"
style="font-size:15px;">

<i class="fas fa-check-circle"></i>

<?= htmlspecialchars($skill['skill_name']) ?>

</span>

<?php

}

}

?>

</div>

</div>

    <!-- Strengths -->
<div class="card mt-4">

    <div class="card-header bg-success">

        <h3 class="card-title">

            <i class="fas fa-check-circle"></i>

            Strengths

        </h3>

    </div>

    <div class="card-body">

<?php

$strengthList = array_filter(array_map("trim", preg_split('/[\r\n\.]+/', $analysis['strengths'])));

if(count($strengthList)==0){

?>

<div class="alert alert-secondary">

No strengths detected.

</div>

<?php

}else{

foreach($strengthList as $item){

?>

<div class="alert alert-success mb-2">

<i class="fas fa-check-circle"></i>

<?= htmlspecialchars($item) ?>

</div>

<?php

}

}

?>

</div>

</div>



<!-- Weaknesses -->

<div class="card mt-4">

    <div class="card-header bg-danger">

        <h3 class="card-title">

            <i class="fas fa-times-circle"></i>

            Weaknesses

        </h3>

    </div>

    <div class="card-body">

<?php

$weakList = array_filter(array_map("trim", preg_split('/[\r\n\.]+/', $analysis['weaknesses'])));

if(count($weakList)==0){

?>

<div class="alert alert-success">

No weaknesses detected.

</div>

<?php

}else{

foreach($weakList as $item){

?>

<div class="alert alert-danger mb-2">

<i class="fas fa-times-circle"></i>

<?= htmlspecialchars($item) ?>

</div>

<?php

}

}

?>

</div>
</div>



<!-- Suggestions -->

<div class="card mt-4">

    <div class="card-header bg-warning">

        <h3 class="card-title text-dark">

            <i class="fas fa-lightbulb"></i>

            Suggestions

        </h3>

    </div>

    <div class="card-body">

<?php

$suggestionList = array_filter(array_map("trim", preg_split('/[\r\n\.]+/', $analysis['suggestions'])));

if(count($suggestionList)==0){

?>

<div class="alert alert-info">

No suggestions available.

</div>

<?php

}else{

foreach($suggestionList as $item){

?>

<div class="alert alert-warning mb-2">

<i class="fas fa-lightbulb"></i>

<?= htmlspecialchars($item) ?>

</div>

<?php

}

}

?>

</div>

</div>

<div class="card mt-4">

    <div class="card-header bg-info">

        <h3 class="card-title">

            Resume Completeness

        </h3>

    </div>

    <div class="card-body">

        <div class="progress progress-lg">

            <div
                class="progress-bar bg-success"
                role="progressbar"
                style="width: <?= $score ?>%;">

                <?= $score ?>%

            </div>

        </div>

        <br>

        <?php

        if($score>=80){

            echo "<strong class='text-success'>Excellent Resume Structure</strong>";

        }
        elseif($score>=60){

            echo "<strong class='text-warning'>Good Resume - Needs Minor Improvements</strong>";

        }
        else{

            echo "<strong class='text-danger'>Resume Needs Significant Improvement</strong>";

        }

        ?>

    </div>

</div>

<hr>

<div class="row text-center">

<div class="col-md-4">

<h5>

<?= $skills->num_rows ?>

</h5>

<small>

Skills

</small>

</div>

<div class="col-md-4">

<h5>

<?= $score ?>%

</h5>

<small>

ATS Score

</small>

</div>

<div class="col-md-4">

<h5>

<?= date("Y") ?>

</h5>

<small>

Analysis Year

</small>

</div>

</div>

<div class="card mt-4">

<div class="card-header bg-dark">

<h3 class="card-title">

ATS Score Breakdown

</h3>

</div>

<div class="card-body">

<canvas id="atsChart" height="120"></canvas>

</div>

</div>


<hr>

<div class="text-center text-muted mb-3">

<h5>

AI Resume Analyzer & ATS Checker

</h5>

<p>

Generated on

<strong>

<?= date("d M Y, h:i A") ?>

</strong>

</p>

<p>

Designed for academic demonstration and ATS evaluation.

</p>

</div>


</div>

<div class="text-center mt-5 mb-5">

<a href="../dashboard/history.php"
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

<script src="../plugins/chart.js/Chart.min.js"></script>

<script>

new Chart(document.getElementById("atsChart"),{

type:'bar',

data:{

labels:[

'ATS Score'

],

datasets:[{

label:'Score',

data:[<?= $score ?>]

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