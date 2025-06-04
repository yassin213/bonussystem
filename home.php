<?php
ob_start();
session_start();
// Set session expiration time in seconds (3 months = 7776000 seconds)
// $session_lifetime = 7776000; 

// // Set session cookie parameters
// ini_set('session.gc_maxlifetime', $session_lifetime); 
// ini_set('session.cookie_lifetime', $session_lifetime); 

// // Start the session
// session_start();

// var_dump($_SERVER['HTTPS']);
// exit;

// Check if session variables are set
if (!isset($_SESSION['partner_code']) || !isset($_SESSION['email'])) {
    // Redirect to login if session variables are not set
    header('Location: index.php');
    exit();
}

$logo_image = "alb_kebap_logo.jpg";

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Treueprogramm</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                    <a class="nav-link" href="#"><?php echo "ID: ".$_SESSION['partner_code']; ?></a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Content -->
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <!-- Responsive Image -->
            <img src="<?php echo $logo_image; ?>" class="img-fluid" alt="Responsive Image">
            <h3>Ihre Treue wird belohnt! 10 + 1 Gratis <span style="font-size:100px;">&#127790;</span></h3>
        </div>
    </div>
</div>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
