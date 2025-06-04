<?php
session_start();

// Get the full URL path from the REQUEST_URI
$urlPath = $_SERVER['REQUEST_URI'];

// Use regex to capture the number (200) in the URL path
if (preg_match("#/dev/(\d+)/admin/#", $urlPath, $matches)) {
    $partner_code = $matches[1];
} else {
    echo "No Partner_code match found. login";
    exit;
}

// Database connection details
$host = "sql686.your-server.de";
$username = "comeba_1";
$password = "py69fFcMjpwQKv3h";
$dbname = "comeba_db1";

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$error = ""; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $captchaInput = $_POST['captcha'] ?? '';

        // Check if CAPTCHA is correct
        if (empty($captchaInput) || $captchaInput !== $_SESSION['captcha']) {
            $error = "Invalid CAPTCHA. Please try again.";
        } elseif(!empty($email) && !empty($password) && !empty($partner_code)) {
        try {
            // Prepare and execute the query
            $stmt = $pdo->prepare("SELECT passwort FROM partner_info WHERE email = :email AND partner_code = :partner_code");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':partner_code', $partner_code);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['passwort'])) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_email'] = $email;
                $_SESSION['partner_code'] = $partner_code;
                $_SESSION['valid'] = true; // Mark session as valid
                $_SESSION['timeout'] = time() + 4500; // Set session to expire in 30 minutes
                header('Location: index.php'); // Redirect to the main page
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
// Clear CAPTCHA after validation
unset($_SESSION['captcha']);
?>

<!DOCTYPE html>
<html lang="de">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Admin</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
                                    <?php endif; ?>
                                    <form class="user" method="POST" action="">
                                    <div class="form-group">
                                        <input type="email" class="form-control form-control-user" name="email" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address..." required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user" name="password" id="exampleInputPassword" placeholder="Password" required>
                                    </div>
                                        <!-- Display the CAPTCHA image -->
                                    <div class="form-group">
                                        <img src="captcha.php" alt="CAPTCHA Image" id="captcha-img">
                                        <button type="button" onclick="document.getElementById('captcha-img').src='captcha.php?' + Math.random()">Reload</button>
                                        <input type="text" class="form-control form-control-user" name="captcha" placeholder="Enter CAPTCHA" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
                                </form>
                                                <hr>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.html">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>