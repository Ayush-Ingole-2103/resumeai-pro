<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="<?php echo BASE_URL;?>">

AI Resume Analyzer

</a>

<div>

<?php if(isset($_SESSION['user_id'])){ ?>

<a href="<?php echo BASE_URL;?>/dashboard/" class="btn btn-outline-light">

Dashboard

</a>

<a href="<?php echo BASE_URL;?>/auth/logout.php" class="btn btn-danger">

Logout

</a>

<?php } else { ?>

<a href="<?php echo BASE_URL;?>/auth/login.php" class="btn btn-outline-light">

Login

</a>

<a href="<?php echo BASE_URL;?>/auth/register.php" class="btn btn-primary">

Register

</a>

<?php } ?>

</div>

</div>

</nav>