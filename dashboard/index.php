<?php
include_once "../includes/dashboard_header.php";
include_once "../config/db.php";
include_once "../includes/dashboard_stats.php";

// Welcome name
$userName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "User";
?>

<div class="content-wrapper">

<section class="content-header">
<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center">

<div>
<h1 class="m-0">Dashboard</h1>
<small class="text-muted">
Welcome back,
<strong><?= htmlspecialchars($userName) ?></strong> 👋
</small>
</div>

<a href="upload.php" class="btn btn-primary">
<i class="fas fa-upload"></i>
Upload Resume
</a>

</div>

</div>
</section>


<section class="content">

<div class="container-fluid">


<!-- Statistics -->

<div class="row">

<div class="col-lg-3 col-6">

<div class="small-box bg-info">

<div class="inner">
<h3><?= $totalResumes ?></h3>
<p>Uploaded Resumes</p>
</div>

<div class="icon">
<i class="fas fa-file-alt"></i>
</div>

</div>

</div>


<div class="col-lg-3 col-6">

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


<div class="col-lg-3 col-6">

<div class="small-box bg-warning">

<div class="inner">
<h3><?= $analyzed ?></h3>
<p>Analyzed Resumes</p>
</div>

<div class="icon">
<i class="fas fa-check-circle"></i>
</div>

</div>

</div>


<div class="col-lg-3 col-6">

<div class="small-box bg-danger">

<div class="inner">
<h3><?= max(0, $totalResumes - $analyzed) ?></h3>
<p>Pending Analysis</p>
</div>

<div class="icon">
<i class="fas fa-clock"></i>
</div>

</div>

</div>

</div>



<!-- Quick Actions -->


<div class="row">

<div class="col-md-12">

<div class="card">

<div class="card-header">

<h3 class="card-title">

Quick Actions

</h3>

</div>

<div class="card-body">

<a href="upload.php" class="btn btn-primary mr-2">

<i class="fas fa-upload"></i>

Upload Resume

</a>


<a href="history.php" class="btn btn-success mr-2">

<i class="fas fa-history"></i>

Resume History

</a>


<a href="#" class="btn btn-warning mr-2">

<i class="fas fa-robot"></i>

ATS Analysis

</a>


<a href="#" class="btn btn-info">

<i class="fas fa-chart-pie"></i>

Analytics

</a>

</div>

</div>

</div>

</div>




<!-- Recent Uploads -->


<div class="row">

<div class="col-md-12">

<div class="card">

<div class="card-header">

<h3 class="card-title">

Recent Uploads

</h3>

</div>

<div class="card-body table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>Resume</th>

<th>Status</th>

<th>Date</th>

</tr>

</thead>

<tbody>

<?php if($recentUploads->num_rows>0){ ?>

<?php while($resume=$recentUploads->fetch_assoc()){ ?>

<tr>

<td>

<?= htmlspecialchars($resume['resume_title']) ?>

</td>

<td>

<?php

if($resume['status']=="Analyzed"){

echo '<span class="badge badge-success">Analyzed</span>';

}else{

echo '<span class="badge badge-warning">Uploaded</span>';

}

?>

</td>

<td>

<?= date("d M Y",strtotime($resume['upload_date'])) ?>

</td>

</tr>

<?php } ?>

<?php } else { ?>

<tr>

<td colspan="3" class="text-center">

No resumes uploaded yet.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>




<!-- Charts -->


<div class="row">

<div class="col-lg-6">

<div class="card">

<div class="card-header">

<h3 class="card-title">

Resume Upload Trend

</h3>

</div>

<div class="card-body">

<canvas id="uploadChart" height="180"></canvas>

</div>

</div>

</div>



<div class="col-lg-6">

<div class="card">

<div class="card-header">

<h3 class="card-title">

ATS Distribution

</h3>

</div>

<div class="card-body">

<canvas id="atsChart" height="180"></canvas>

</div>

</div>

</div>

</div>



</div>

</section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const uploadChart=new Chart(
document.getElementById('uploadChart'),
{
type:'bar',
data:{
labels:['Jan','Feb','Mar','Apr','May','Jun'],
datasets:[{
label:'Uploads',
data:[0,0,0,0,0,<?= $totalResumes ?>]
}]
}
});

const atsChart=new Chart(
document.getElementById('atsChart'),
{
type:'doughnut',
data:{
labels:['Average ATS','Remaining'],
datasets:[{
data:[
<?= round($avgATS) ?>,
<?= 100-round($avgATS) ?>
]
}]
}
});

</script>

<?php
include_once "../includes/dashboard_footer.php";
?>