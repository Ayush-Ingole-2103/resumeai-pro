<?php
include_once "../includes/dashboard_header.php";
include_once "../config/db.php";

$user_id = $_SESSION['user_id'];

// Delete Resume
if(isset($_GET['delete'])){

    $resume_id = (int)$_GET['delete'];

    // Get file path
    $stmt = $conn->prepare("SELECT file_path FROM resumes WHERE resume_id=? AND user_id=?");
    $stmt->bind_param("ii",$resume_id,$user_id);
    $stmt->execute();

    $result=$stmt->get_result();

    if($result->num_rows>0){

        $resume=$result->fetch_assoc();

        if(file_exists("../".$resume['file_path'])){
            unlink("../".$resume['file_path']);
        }

        $delete=$conn->prepare("DELETE FROM resumes WHERE resume_id=? AND user_id=?");
        $delete->bind_param("ii",$resume_id,$user_id);
        $delete->execute();

        echo "<script>
        alert('Resume deleted successfully.');
        window.location='history.php';
        </script>";

        exit;
    }
}

// Fetch resumes
$stmt=$conn->prepare("SELECT * FROM resumes WHERE user_id=? ORDER BY upload_date DESC");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$resumes=$stmt->get_result();
?>

<div class="content-wrapper">

<section class="content-header">

<div class="container-fluid">

<h1>Resume History</h1>

</div>

</section>

<section class="content">

<div class="container-fluid">

<div class="card">

<div class="card-header">

<h3 class="card-title">

Uploaded Resumes

</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead class="thead-dark">

<tr>

<th>#</th>

<th>Title</th>

<th>Type</th>

<th>Uploaded</th>

<th>Status</th>

<th>ATS Score</th>

<th width="220">Actions</th>

</tr>

</thead>

<tbody>

<?php

$count=1;

while($row=$resumes->fetch_assoc()){

?>

<tr>

<td><?= $count++; ?></td>

<td><?= htmlspecialchars($row['resume_title']); ?></td>

<td><?= htmlspecialchars($row['resume_type']); ?></td>

<td><?= date("d M Y",strtotime($row['upload_date'])); ?></td>

<td>

<span class="badge badge-info">

<?= $row['status']; ?>

</span>

</td>

<td>

<?php

if($row['ats_score']){

echo $row['ats_score']."%";

}else{

echo "--";

}

?>

</td>

<td>

<a
href="../<?= $row['file_path']; ?>"
class="btn btn-success btn-sm"
download>

<i class="fas fa-download"></i>

</a>

<a
href="?delete=<?= $row['resume_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this resume?')">

<i class="fas fa-trash"></i>

</a>

<a
href="../analysis/analyze.php?id=<?= $row['resume_id']; ?>"
class="btn btn-success btn-sm">

<i class="fas fa-search"></i> Analyze

</a>

</td>

</tr>

<?php
}
?>

</tbody>

</table>

</div>

</div>

</div>

</section>

</div>

<?php
include_once "../includes/dashboard_footer.php";
?>