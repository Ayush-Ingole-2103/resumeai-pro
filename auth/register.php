<?php
include_once "../config/config.php";
include_once "../config/db.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get Form Data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ==========================
    // Validation
    // ==========================

    if (empty($full_name)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // ==========================
    // Check Existing Email
    // ==========================

    if (empty($errors)) {

        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = "Email already registered.";
        }

        $check->close();
    }

    // ==========================
    // Insert User
    // ==========================

    if (empty($errors)) {

        // Secure Password Hashing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $role = "user";

        $stmt = $conn->prepare("INSERT INTO users(full_name,email,password,role) VALUES(?,?,?,?)");

        $stmt->bind_param(
            "ssss",
            $full_name,
            $email,
            $hashedPassword,
            $role
        );

        if ($stmt->execute()) {

            $success = "Registration Successful! Redirecting to Login...";

            header("refresh:2;url=login.php");

        } else {

            $errors[] = "Something went wrong. Please try again.";

        }

        $stmt->close();
    }
}
?>

<?php include_once "../includes/header.php"; ?>
<?php include_once "../includes/navbar.php"; ?>

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-md-6">

            <div class="card shadow-lg">

                <div class="card-body p-5">

                    <h2 class="text-center mb-4">
                        Create Account
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

                    <?php if($success!=""){ ?>

                        <div class="alert alert-success">

                            <?php echo $success; ?>

                        </div>

                    <?php } ?>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="full_name"
                                value="<?php echo isset($full_name)?htmlspecialchars($full_name):''; ?>"
                                required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Email Address

                            </label>

                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                value="<?php echo isset($email)?htmlspecialchars($email):''; ?>"
                                required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Password

                            </label>

                            <input
                                type="password"
                                class="form-control"
                                name="password"
                                required>

                        </div>

                        <div class="mb-4">

                            <label class="form-label">

                                Confirm Password

                            </label>

                            <input
                                type="password"
                                class="form-control"
                                name="confirm_password"
                                required>

                        </div>

                        <button class="btn btn-primary w-100">

                            Register

                        </button>

                    </form>

                    <hr>

                    <p class="text-center">

                        Already have an account?

                        <a href="login.php">

                            Login Here

                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include_once "../includes/footer.php"; ?>