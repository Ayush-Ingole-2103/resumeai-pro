<?php

include_once "../includes/dashboard_header.php";
include_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===========================================
   Total Resumes
=========================================== */

$stmt = $conn->prepare("
SELECT COUNT(*) AS total
FROM resumes
WHERE user_id=?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$totalResumes = $stmt->get_result()->fetch_assoc()['total'];

/* ===========================================
   Average ATS
=========================================== */

$stmt = $conn->prepare("
SELECT AVG(ats_score) AS avg_score
FROM resumes
WHERE user_id=?
AND ats_score IS NOT NULL
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$avgATS = $stmt->get_result()->fetch_assoc()['avg_score'];

if ($avgATS === null) {
    $avgATS = 0;
}

/* ===========================================
   Highest ATS
=========================================== */

$stmt = $conn->prepare("
SELECT MAX(ats_score) AS highest
FROM resumes
WHERE user_id=?
AND ats_score IS NOT NULL
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$highestATS = $stmt->get_result()->fetch_assoc()['highest'];

if ($highestATS === null) {
    $highestATS = 0;
}

/* ===========================================
   Lowest ATS
=========================================== */

$stmt = $conn->prepare("
SELECT MIN(ats_score) AS lowest
FROM resumes
WHERE user_id=?
AND ats_score IS NOT NULL
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$lowestATS = $stmt->get_result()->fetch_assoc()['lowest'];

if ($lowestATS === null) {
    $lowestATS = 0;
}

/* ===========================================
   Total Analyzed
=========================================== */

$stmt = $conn->prepare("
SELECT COUNT(*) AS analyzed
FROM resumes
WHERE user_id=?
AND status='Analyzed'
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$analyzed = $stmt->get_result()->fetch_assoc()['analyzed'];

?>

<div class="content-wrapper">

<section class="content-header">

<div class="container-fluid">

<h1 class="mt-2">

Resume Analytics

</h1>

</div>

</section>

<section class="content">

<div class="container-fluid">

<div class="row">

<!-- Total Resumes -->

<div class="col-lg-3 col-md-6">

<div class="small-box bg-info">

<div class="inner">

<h3><?= $totalResumes ?></h3>

<p>Total Resumes</p>

</div>

<div class="icon">

<i class="fas fa-file-alt"></i>

</div>

</div>

</div>

<!-- Average ATS -->

<div class="col-lg-3 col-md-6">

<div class="small-box bg-success">

<div class="inner">

<h3><?= number_format($avgATS,1) ?>%</h3>

<p>Average ATS Score</p>

</div>

<div class="icon">

<i class="fas fa-chart-line"></i>

</div>

</div>

</div>

<!-- Highest ATS -->

<div class="col-lg-3 col-md-6">

<div class="small-box bg-warning">

<div class="inner">

<h3><?= number_format($highestATS,0) ?>%</h3>

<p>Highest ATS Score</p>

</div>

<div class="icon">

<i class="fas fa-trophy"></i>

</div>

</div>

</div>

<!-- Total Analyzed -->

<div class="col-lg-3 col-md-6">

<div class="small-box bg-danger">

<div class="inner">

<h3><?= $analyzed ?></h3>

<p>Analyzed Resumes</p>

</div>

<div class="icon">

<i class="fas fa-check-circle"></i>

</div>

</div>

</div>

</div>

<?php

/* ===========================================
   ATS Score Distribution
=========================================== */

$stmt = $conn->prepare("
SELECT resume_title, ats_score
FROM resumes
WHERE user_id=?
AND ats_score IS NOT NULL
ORDER BY upload_date ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$labels = [];
$scores = [];

while($row = $result->fetch_assoc()){

    $labels[] = substr($row['resume_title'],0,20);
    $scores[] = (float)$row['ats_score'];

}

if(empty($labels)){
    $labels = ["No Resume"];
    $scores = [0];
}

?>

<div class="row">

<div class="col-md-12">

<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">

ATS Score Distribution

</h3>

</div>

<div class="card-body">

<canvas id="atsChart" height="90"></canvas>

</div>

</div>

</div>

</div>

<?php

/* ===========================================
   Top Skills
=========================================== */

$stmt = $conn->prepare("
SELECT
skills.skill_name,
COUNT(*) total

FROM resume_skills

INNER JOIN skills
ON skills.skill_id = resume_skills.skill_id

INNER JOIN resumes
ON resumes.resume_id = resume_skills.resume_id

WHERE resumes.user_id=?

GROUP BY skills.skill_name

ORDER BY total DESC

LIMIT 10
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$topSkills = $stmt->get_result();

?>

<div class="row">

<div class="col-md-6">

<div class="card" style="min-height:420px;">

<div class="card-header bg-success">

<h3 class="card-title">

Top Skills Used

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>Skill</th>

<th>Total</th>

</tr>

</thead>

<tbody>

<?php

if($topSkills->num_rows==0){

?>

<tr>

<td colspan="2" class="text-center text-muted">

No Skills Found

</td>

</tr>

<?php

}else{

while($skill=$topSkills->fetch_assoc()){

?>

<tr>

<td><?= htmlspecialchars($skill['skill_name']) ?></td>

<td><?= $skill['total'] ?></td>

</tr>

<?php

}

}

?>

</tbody>

</table>

</div>

</div>

</div>

<?php

/* ===========================================
   Recent Analysis
=========================================== */

$stmt = $conn->prepare("
SELECT
resume_title,
ats_score,
upload_date
FROM resumes
WHERE user_id=?
AND status='Analyzed'
ORDER BY upload_date DESC
LIMIT 5
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$recent = $stmt->get_result();

?>

<div class="col-md-6">

<div class="card" style="min-height:420px;">

<div class="card-header bg-info">

<h3 class="card-title">

Recent Resume Analysis

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>Resume</th>

<th>ATS</th>

<th>Date</th>

</tr>

</thead>

<tbody>

<?php

if($recent->num_rows==0){

?>

<tr>

<td colspan="3" class="text-center text-muted">

No Analysis Available

</td>

</tr>

<?php

}else{

while($row=$recent->fetch_assoc()){

?>

<tr>

<td><?= htmlspecialchars($row['resume_title']) ?></td>

<td>

<span class="badge badge-success">

<?= number_format($row['ats_score'],0) ?>%

</span>

</td>

<td>

<?= date("d M Y",strtotime($row['upload_date'])) ?>

</td>

</tr>

<?php

}

}

?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<?php

/* ===========================================
   Monthly Resume Uploads
=========================================== */

$stmt = $conn->prepare("
SELECT
DATE_FORMAT(upload_date,'%b') AS month,
COUNT(*) AS total
FROM resumes
WHERE user_id=?
GROUP BY YEAR(upload_date), MONTH(upload_date)
ORDER BY YEAR(upload_date), MONTH(upload_date)
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$result = $stmt->get_result();

$months = [];
$uploads = [];

while($row = $result->fetch_assoc()){

    $months[] = $row['month'];
    $uploads[] = (int)$row['total'];

}

if(empty($months)){

    $months = ["No Data"];
    $uploads = [0];

}

?>

<div class="row">

<div class="col-md-6">

<div class="card">

<div class="card-header bg-warning">

<h3 class="card-title">

Monthly Resume Uploads

</h3>

</div>

<div class="card-body">

<canvas id="monthlyChart" height="120"></canvas>

</div>

</div>

</div>

<?php

/* ===========================================
   Resume Status Distribution
=========================================== */

$stmt = $conn->prepare("
SELECT
status,
COUNT(*) total
FROM resumes
WHERE user_id=?
GROUP BY status
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$result = $stmt->get_result();

$statusLabels = [];
$statusData = [];

while($row = $result->fetch_assoc()){

    $statusLabels[] = $row['status'];
    $statusData[] = (int)$row['total'];

}

if(empty($statusLabels)){

    $statusLabels = ["Uploaded"];
    $statusData = [0];

}

?>

<div class="col-md-6">

<div class="card">

<div class="card-header bg-danger">

<h3 class="card-title">

Resume Status

</h3>

</div>

<div class="card-body">

<canvas id="statusChart" height="120"></canvas>

</div>

</div>

</div>

</div>

<?php

/* ===========================================
   ATS Score Categories
=========================================== */

$excellent = 0;
$good = 0;
$poor = 0;

$stmt = $conn->prepare("
SELECT ats_score
FROM resumes
WHERE user_id=?
AND ats_score IS NOT NULL
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$result = $stmt->get_result();

while($row = $result->fetch_assoc()){

    $score = (float)$row['ats_score'];

    if($score >= 80){

        $excellent++;

    }
    elseif($score >= 60){

        $good++;

    }
    else{

        $poor++;

    }

}

?>

<div class="row">

<div class="col-md-12">

<div class="card">

<div class="card-header bg-dark">

<h3 class="card-title">

ATS Score Categories

</h3>

</div>

<div class="card-body">

<div class="row text-center">

<div class="col-md-4">

<h2 class="text-success">

<?= $excellent ?>

</h2>

<p><strong>Excellent</strong></p>

<small>ATS Score ≥ 80%</small>

</div>

<div class="col-md-4">

<h2 class="text-warning">

<?= $good ?>

</h2>

<p><strong>Good</strong></p>

<small>ATS Score 60–79%</small>

</div>

<div class="col-md-4">

<h2 class="text-danger">

<?= $poor ?>

</h2>

<p><strong>Needs Improvement</strong></p>

<small>ATS Score &lt; 60%</small>

</div>

</div>

</div>

</div>

</div>

</div>

<!-- ===========================================
     Chart.js
=========================================== -->

<script src="../plugins/chart.js/Chart.min.js"></script>

<script>

/* ===========================================
   ATS Score Distribution
=========================================== */

new Chart(document.getElementById("atsChart"),{

    type:'bar',

    data:{

        labels:<?= json_encode($labels) ?>,

        datasets:[{

            label:'ATS Score',

            data:<?= json_encode($scores) ?>,

            backgroundColor:[
                '#17a2b8',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6f42c1',
                '#20c997',
                '#fd7e14',
                '#007bff',
                '#6610f2',
                '#e83e8c'
            ],

            borderWidth:1

        }]

    },

    options:{

        responsive:true,

        maintainAspectRatio:false,

        plugins:{
            legend:{
                display:false
            }
        },

        scales:{

            y:{

                beginAtZero:true,

                max:100,

                ticks:{
                    stepSize:10
                }

            }

        }

    }

});


/* ===========================================
   Monthly Uploads
=========================================== */

new Chart(document.getElementById("monthlyChart"),{

    type:'line',

    data:{

        labels:<?= json_encode($months) ?>,

        datasets:[{

            label:'Uploads',

            data:<?= json_encode($uploads) ?>,

            borderColor:'#007bff',

            backgroundColor:'rgba(0,123,255,0.15)',

            fill:true,

            tension:0.3

        }]

    },

    options:{

        responsive:true,

        maintainAspectRatio:false

    }

});


/* ===========================================
   Resume Status
=========================================== */

new Chart(document.getElementById("statusChart"),{

    type:'doughnut',

    data:{

        labels:<?= json_encode($statusLabels) ?>,

        datasets:[{

            data:<?= json_encode($statusData) ?>,

            backgroundColor:[

                '#28a745',

                '#ffc107',

                '#dc3545',

                '#17a2b8'

            ]

        }]

    },

    options:{

        responsive:true,

        maintainAspectRatio:false

    }

});

</script>

</div>

</section>

</div>

<?php
include_once "../includes/dashboard_footer.php";
?>