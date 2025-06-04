<?php
ob_start();
session_start();

// Check if session variables are set
if (!isset($_SESSION['partner_code']) || !isset($_SESSION['email'])) {
    // Redirect to login if session variables are not set
    header('Location: index.php');
    exit();
}
$currentDate = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$currentMonth = date('d.m.Y');
$testUserId = 1800;

require_once "db-connect.php";
require_once "Classes/Database.php";

    $userId = isset($_GET['user_id']) && filter_var($_GET['user_id'], FILTER_VALIDATE_INT) ? $_GET['user_id'] : null;
    $partnerCode = isset($_GET['partner_code']) && filter_var($_GET['partner_code'], FILTER_VALIDATE_INT) ? $_GET['partner_code'] : null;

    $db = new Database($servername, $dbname, $username, $password);

    $validatedData = $db->validatePartnerCodeAndUserId($partnerCode, $userId);

    if (!$validatedData) {
        echo "<div id='message' style='
        color: red;
        font-weight: bold;
        font-size: 50px;
        padding: 10px;
        border: 2px solid red;
        background-color: #fdd;
        max-width: 500px;
        margin: 20px auto;
        text-align: center;
      '>
       Invalid User ID OR Partner Code. 
      </div>";
    
        echo '<div class="container text-center" style="margin-top: 20px;">
        <h1><span id="countdown">15</span> Seconds.</h1>
        </div>';
    
        // Countdown script
        echo "<script>
        var countdownElement = document.getElementById('countdown');
        var countdownValue = 15;
    
        function updateCountdown() {
            countdownValue--;
            countdownElement.textContent = countdownValue ;
    
            if (countdownValue <= 0) {
                window.location.href = 'home.php'; // Redirect after 15 seconds
            }
        }
    
        var countdownInterval = setInterval(updateCountdown, 1000); // Update every second
        </script>";
    
        exit(); // Ensure PHP stops executing
    }

    // Check if the user is a test user
    // $isTestUser = ($userId == $testUserId);

    // // Check if user has already received points today
    // if (!$isTestUser) {
    //     $row = $db->hasReceivedPointsToday($userId, $partnerCode, $currentDate);
    // } else {
    //     $row = false; // Skip the daily check for test user
    // }
#######################
// if ($row) {

//         // User has already received points today, do not add more
//         echo "<div id='message' style='
//                 color: red;
//                 font-weight: bold;
//                 font-size: 50px;
//                 padding: 10px;
//                 border: 2px solid red;
//                 background-color: #fdd;
//                 max-width: 500px;
//                 margin: 20px auto;
//                 text-align: center;
//               '>
//                Du hast heute schon einen Punkt erhalten! Bitte komm morgen wieder. 
//               </div>";
//               echo '<audio autoplay>
//               <source src="faild.mp3" type="audio/mp3">
//               Your browser does not support the audio element.
//             </audio>';

//         echo "<script>
//                 setTimeout(function() {
//                     window.location.replace('home.php');   
                  
//                 }, 15000);  // Redirect after 15 seconds
//               </script>";

//         echo "<div class='container text-center' style='margin-top: 2px;  font-size: 50px; text-align: center; font-weight: bold;' >
//               <h1> <span id='countdown'>15</span> seconds.</h1>
//               </div> ";
//     exit();
//             }
  


 // Check user points
 $userPoints = $db->getUserPoints($userId, $partnerCode);

 if ($userPoints) {
     $newPoints = min($userPoints['point'] + 1, 11);

     if ($newPoints == 11) {
         $db->resetPointsAndAddReward($userId, $partnerCode, $currentMonth);
         $_SESSION['reward_eligible'] = true;
         header("Location: reward-effect.php");
         exit();
     } else {
         $db->updatePoints($userId, $partnerCode, $newPoints, $timestamp);
         $imagePath = "images/$newPoints.jpg";
     }
    } else {
        $db->AddPoint($userId, $partnerCode, $timestamp);
        $imagePath = "images/1.jpg";
    }
    

 if (file_exists($imagePath)) {
    echo "<div class='text-center mt-3'>
            <img src='$imagePath' class='img-fluid rounded' alt='Points Image'>
          </div>";

    // Add sound effect (this plays a sound when the image is displayed)
    echo "<audio id='soundEffect' src='cash_effect.mp3' preload='auto'></audio>
          <script>
            // Play the sound effect when the image is displayed
            document.getElementById('soundEffect').play();
          </script>";
}else {
     echo "<div class='alert alert-info text-center mt-3'>
             <h4>You now have $newPoints points, but no image found.</h4>
           </div>";

     // Add sound effect (this plays a sound when the image is displayed)
    echo "<audio id='soundEffect' src='cash_effect.mp3' preload='auto'></audio>
    <script>
      // Play the sound effect when the image is displayed
      document.getElementById('soundEffect').play();
    </script>";
 

    echo "<script>
            setTimeout(function() {
                window.location.href = 'home.php';
            }, 30000);
        </script>";
}
?>




