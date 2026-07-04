<?php
include_once "../includes/dashboard_header.php";
include_once "../config/db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];

    $resume_title = trim($_POST['resume_title']);
    $resume_type = trim($_POST['resume_type']);
    $notes = trim($_POST['notes']);

    if (empty($resume_title)) {
        $message = "Resume title is required.";
        $messageType = "danger";
    }

    elseif (!isset($_FILES['resume']) || $_FILES['resume']['error'] != 0) {
        $message = "Please select a resume.";
        $messageType = "danger";
    }

    else {

        $allowed = ['pdf', 'doc', 'docx'];

        $fileName = $_FILES['resume']['name'];
        $fileTmp = $_FILES['resume']['tmp_name'];
        $fileSize = $_FILES['resume']['size'];

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed)) {

            $message = "Only PDF, DOC and DOCX files are allowed.";
            $messageType = "danger";

        } elseif ($fileSize > 5 * 1024 * 1024) {

            $message = "Maximum file size is 5MB.";
            $messageType = "danger";

        } else {

            $newFileName = uniqid("resume_", true) . "." . $extension;

            $uploadDir = "../assets/uploads/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destination)) {

                $dbPath = "assets/uploads/" . $newFileName;

                $stmt = $conn->prepare("
                    INSERT INTO resumes
                    (
                        user_id,
                        resume_title,
                        resume_type,
                        file_name,
                        file_path,
                        file_size,
                        file_type,
                        notes
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "issssiss",
                    $user_id,
                    $resume_title,
                    $resume_type,
                    $fileName,
                    $dbPath,
                    $fileSize,
                    $extension,
                    $notes
                );

                if ($stmt->execute()) {

                    $message = "Resume uploaded successfully!";
                    $messageType = "success";

                } else {

                    $message = "Database error.";
                    $messageType = "danger";

                }

            } else {

                $message = "Unable to upload file.";
                $messageType = "danger";

            }

        }

    }

}
?>

<div class="content-wrapper">

<section class="content-header">

<div class="container-fluid">

<div class="row mb-2">

<div class="col-sm-6">

<h1>Upload Resume</h1>

</div>

</div>

</div>

</section>

<section class="content">

<div class="container-fluid">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card card-primary">

<div class="card-header">

<h3 class="card-title">

Upload Your Resume

</h3>

</div>

<form method="POST" enctype="multipart/form-data">

<div class="card-body">

<?php if($message!=""){ ?>

<div class="alert alert-<?php echo $messageType; ?>">

<?php echo $message; ?>

</div>

<?php } ?>

<div class="form-group">

<label>Resume Title</label>

<input
type="text"
name="resume_title"
class="form-control"
placeholder="Example: Software Engineer Resume"
required>

</div>

<div class="form-group">

<label>Resume Type</label>

<select
name="resume_type"
class="form-control">

<option>Software Engineer</option>

<option>AI Engineer</option>

<option>Data Analyst</option>

<option>Web Developer</option>

<option>Java Developer</option>

<option>Python Developer</option>

<option>Cloud Engineer</option>

<option>Other</option>

</select>

</div>

<div class="form-group">

<label>Upload Resume</label>

<div class="custom-file">

<input
type="file"
name="resume"
class="custom-file-input"
required>

<label class="custom-file-label">

Choose Resume

</label>

</div>

</div>

<div class="form-group">

<label>Notes</label>

<textarea
name="notes"
rows="4"
class="form-control"
placeholder="Optional notes..."></textarea>

</div>

</div>

<div class="card-footer text-center">

<button
type="submit"
class="btn btn-primary btn-lg">

<i class="fas fa-upload"></i>

Upload Resume

</button>

</div>

</form>

</div>

</div>

</div>

</div>

</section>

</div>

<script>

document.addEventListener("DOMContentLoaded",function(){

const fileInput=document.querySelector(".custom-file-input");

if(fileInput){

fileInput.addEventListener("change",function(){

let fileName=this.files[0].name;

this.nextElementSibling.innerHTML=fileName;

});

}

});

</script>

<?php
include_once "../includes/dashboard_footer.php";
?>