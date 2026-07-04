<?php
include_once "../config/config.php";
include_once "../config/db.php";

// If already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit();
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    }

    if (empty($errors)) {

        $stmt = $conn->prepare("SELECT user_id, full_name, email, password, role FROM users WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 1){

            $user = $result->fetch_assoc();

            if(password_verify($password,$user['password'])){
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header("Location: ../dashboard/index.php");
                exit();

            }else{

                $errors[] = "Invalid Email or Password.";

            }

        }else{

            $errors[] = "Invalid Email or Password.";

        }

        $stmt->close();

    }

}
?>

<?php include_once "../includes/header.php"; ?>
<?php include_once "../includes/navbar.php"; ?>

<div class="container">

<div class="row justify-content-center mt-5">

<div class="col-md-5">

<div class="card shadow-lg">

<div class="card-body p-5">

<h2 class="text-center mb-4">

Welcome Back 👋

</h2>

<?php if(!empty($errors)){ ?>

<div class="alert alert-danger">

<ul class="mb-0">

<?php foreach($errors as $error){ ?>

<li><?php echo $error; ?></li>

<?php } ?>

</ul>

</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label>Email</label>

<input
type="email"
class="form-control"
name="email"
required>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
class="form-control"
id="password"
name="password"
required>

</div>

<div class="form-check mb-4">

<input
class="form-check-input"
type="checkbox"
onclick="togglePassword()"
id="showPassword">

<label class="form-check-label">

Show Password

</label>

</div>

<button class="btn btn-primary w-100">

Login

</button>

</form>

<hr>

<p class="text-center">

Don't have an account?

<a href="register.php">

Register Here

</a>

</p>

</div>

</div>

</div>

</div>

</div>

<script>

function togglePassword(){

let pass=document.getElementById("password");

if(pass.type==="password"){

pass.type="text";

}else{

pass.type="password";

}

}

</script>

<?php include_once "../includes/footer.php"; ?>