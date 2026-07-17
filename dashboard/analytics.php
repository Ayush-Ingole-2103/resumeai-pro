<?php

include_once "../includes/dashboard_header.php";
include_once "../config/db.php";

$user_id = $_SESSION['user_id'];

/* ===========================================
   Total Resumes
=========================================== */

$stmt = $conn->prepare("
SELECT COUNT(*) total
FROM resumes
WHERE user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$totalResumes = $stmt->get_result()->fetch_assoc()['total'];


/* ===========================================
   Average ATS
=========================================== */

$stmt = $conn->prepare("
SELECT AVG(ats_score) avgscore
FROM resumes
WHERE user_id=?
AND ats_score IS NOT NULL
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$avgATS = $stmt->get_result()->fetch_assoc()['avgscore'];

if(!$avgATS)
    $avgATS = 0;


/* ===========================================
   Highest ATS
=========================================== */

$stmt = $conn->prepare("
SELECT MAX(ats_score) highest
FROM resumes
WHERE user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$highestATS = $stmt->get_result()->fetch_assoc()['highest'];

if(!$highestATS)
    $highestATS = 0;


/* ===========================================
   Total Analyzed
=========================================== */

$stmt = $conn->prepare("
SELECT COUNT(*) analyzed
FROM resumes
WHERE user_id=?
AND status='Analyzed'
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$analyzed = $stmt->get_result()->fetch_assoc()['analyzed'];

?>

<div class="content-wrapper">

<section class="content-header">

<div class="container-fluid">

<h1>

Resume Analytics

</h1>

</div>

</section>

<section class="content">

<div class="container-fluid">

<div class="row">


<div class="col-lg-3">

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



<div class="col-lg-3">

<div class="small-box bg-success">

<div class="inner">

<h3><?= number_format($avgATS,1) ?>%</h3>

<p>Average ATS</p>

</div>

<div class="icon">

<i class="fas fa-chart-line"></i>

</div>

</div>

</div>



<div class="col-lg-3">

<div class="small-box bg-warning">

<div class="inner">

<h3><?= $highestATS ?>%</h3>

<p>Highest ATS</p>

</div>

<div class="icon">

<i class="fas fa-trophy"></i>

</div>

</div>

</div>



<div class="col-lg-3">

<div class="small-box bg-danger">

<div class="inner">

<h3><?= $analyzed ?></h3>

<p>Analyzed</p>

</div>

<div class="icon">

<i class="fas fa-check-circle"></i>

</div>

</div>

</div>

</div>

</div>

</section>

</div>

<?php
include_once "../includes/dashboard_footer.php";
?>