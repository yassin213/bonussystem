<?php
ob_start();
session_start();
// echo "Input: ".$input ="SAE!hgdtr98";
// echo "<br>";
// $hashedPassword = password_hash($input, PASSWORD_DEFAULT);
// echo "<br>";
// echo "Hash: ".$hashedPassword;
// echo "<br>";
// echo password_verify($input, $hashedPassword); $input as password in klar text hier and not hashed form!

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //$partnerCode = $_POST['partner_code'];
         $partnerPassword = trim($_POST['password']);
        //echo $password = htmlspecialchars($password);
         $email = trim($_POST['email']);
         $email = htmlspecialchars($email);

       // Get the full URL path from the REQUEST_URI
        $urlPath = $_SERVER['REQUEST_URI'];

         // Use a regular expression to capture the partner_code from the URL
         if (preg_match('/\/dev\/(\d+)\//', $urlPath, $matches)) {
            $partnerCode = $matches[1];  // The partner_code will be in the first capture group
            $partnerCode = htmlspecialchars($partnerCode);

            // Ensure the partnerCode is numeric
            if (is_numeric($partnerCode)) {
               // Optionally set it in the session
               $_SESSION['partner_code'] = $partnerCode;
            } else {
               $partnerCode = "Error! Partner code is not numeric.";
            }
         } else {
            $partnerCode = "Error! Partner code not found.";
         }


    // Include the Database class
        require_once "db-connect.php";
        require_once "Classes/Database.php";


    // Initialize the Database object
    $db = new Database($servername, $dbname, $username, $password);
   
    
    // Call the userLogin method
    $user = $db->userLogin($partnerCode, $partnerPassword, $email);
   //  echo 'Stored hash: ' . $user['passwort'] . PHP_EOL;
   //  echo 'Submitted password: ' . $password . PHP_EOL;
   //  var_dump(password_verify($password, $user['passwort']));
   

    if ($user) {
        // Set session variables
        $_SESSION['partner_code'] = $user['partner_code'];
        $_SESSION['email'] = $user['email'];
        // Redirect to index page after successful login
        header('Location: home.php');
        exit();
    } else {
        // Invalid credentials
        $error_message = 'Invalid email or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <title>Login Page</title>

 

</head>
<body>
   <div class="container">
      <div class="row justify-content-center align-items-center" style="height: 100vh;">
         <div class="col-12 col-md-8 col-lg-5">

            <div class="card shadow p-4">
               <h2 class="text-center">Enter Username and Password</h2>
 
               <h4 class="text-danger text-center mt-3">
               <?php if (!empty($error_message)): ?>
                <div style="color: red;"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
               </h4>
               <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="mt-4">
                  <div class="form-group">
                     <label for="email">Email:</label>
                     <input type="email" class="form-control" name="email" id="email" required>
                  </div>
                  <div class="form-group">
                     <label for="password">Password:</label>
                     <input type="password" class="form-control" name="password" id="password" required>
                  </div>
                  <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
               </form>
               <div class="text-center mt-3">
                  <a href="logout.php" title="Logout">Click here to clean Session.</a>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Bootstrap JS and dependencies -->
   <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   
  

</body>
</html>


