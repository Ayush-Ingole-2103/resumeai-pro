<?php
include_once "../includes/dashboard_header.php";
include_once "../config/db.php";
include_once "../includes/dashboard_stats.php";
?>

<div class="content-wrapper">

<section class="content-header">

<div class="container-fluid">

<h1 class="mt-3">

Dashboard

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

<p>Uploaded Resumes</p>

</div>

<div class="icon">

<i class="fas fa-file-alt"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-success">

<div class="inner">

<h3><?= number_format($avgScore,1) ?>%</h3>

<p>ATS Score</p>

</div>

<div class="icon">

<i class="fas fa-chart-line"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-warning">

<div class="inner">

<h3>--</h3>

<p>Skills Found</p>

</div>

<div class="icon">

<i class="fas fa-code"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-danger">

<div class="inner">

<h3>--</h3>

<p>Job Matches</p>

</div>

<div class="icon">

<i class="fas fa-briefcase"></i>

</div>

</div>

</div>
<div class="row">

<div class="col-md-12">

<div class="card">

<div class="card-header">

<h3 class="card-title">
Recent Uploads
</h3>

</div>

<div class="card-body">

<table class="table table-striped">

<thead>

<tr>

<th>Resume</th>

<th>Upload Date</th>

</tr>

</thead>

<tbody>

<?php while($resume = $recentUploads->fetch_assoc()) { ?>

<tr>

<td><?= htmlspecialchars($resume['resume_title']) ?></td>

<td><?= date("d M Y", strtotime($resume['upload_date'])) ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

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