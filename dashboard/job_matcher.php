<?php

include_once "../includes/dashboard_header.php";
include_once "../config/db.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
SELECT
resume_id,
resume_title,
ats_score
FROM resumes
WHERE user_id=?
ORDER BY upload_date DESC
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$resumes = $stmt->get_result();

?>

<div class="content-wrapper">

<section class="content-header">

<div class="container-fluid">

<h1>

Job Matcher

</h1>

</div>

</section>

<section class="content">

<div class="container-fluid">

<div class="row justify-content-center">

<div class="col-lg-10">

<div class="card card-primary">

<div class="card-header">

<h3 class="card-title">

Match Resume With Job Description

</h3>

</div>

<form
method="POST"
action="../analysis/job_match_report.php">

<div class="card-body">

<div class="form-group">

<label>

Select Resume

</label>

<select
name="resume_id"
class="form-control"
required>

<option value="">

Choose Resume

</option>

<?php

while($row=$resumes->fetch_assoc()){

?>

<option value="<?= $row['resume_id'] ?>">

<?= htmlspecialchars($row['resume_title']) ?>

(ATS :
<?= $row['ats_score'] ?? 0 ?>%)

</option>

<?php

}

?>

</select>

</div>

<div class="form-group mt-4">

<label>

Paste Job Description

</label>

<textarea

name="job_description"

class="form-control"

rows="15"

placeholder="Paste the complete Job Description here..."

required

></textarea>

</div>

</div>

<div class="card-footer">

<button
type="submit"
class="btn btn-success">

<i class="fas fa-search"></i>

Match Resume

</button>

</div>

</form>

</div>

</div>

</div>

</div>

</section>

</div>

<?php
include_once "../includes/dashboard_footer.php";
?>