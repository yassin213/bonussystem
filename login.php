<?php
   ob_start();
   session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <title>Login</title>
</head>
<body>
   <div class="container">
      <div class="row justify-content-center align-items-center" style="height: 100vh;">
         <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow p-4">
               <h2 class="text-center">Enter Username and Password</h2>
               <?php
                  $msg = '';
                  $users = ['partner1@gmail.com'=>"14541Saq!"];

                  if (isset($_POST['login']) && !empty($_POST['username']) 
                  && !empty($_POST['password'])) {
                     $user=$_POST['username'];                  
                     if (array_key_exists($user, $users)){
                        if ($users[$_POST['username']]==$_POST['password']){
                           $_SESSION['valid'] = true;
                           $_SESSION['timeout'] = time();
                           $_SESSION['username'] = $_POST['username'];
                           $msg = "You have entered correct username and password";
                           header("Location: /dev/100/home.php "); // Redirect to authorized access page
                           exit();

                        }
                        else {
                           $msg = "You have entered wrong Password";
                        }
                     }
                     else {
                        $msg = "You have entered wrong user name";
                     }
                  }
               ?>
               <h4 class="text-danger text-center mt-3"><?php echo $msg; ?></h4>
               <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="mt-4">
                  <div class="form-group">
                     <label for="username">Username:</label>
                     <input type="text" class="form-control" name="username" id="username" required>
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
