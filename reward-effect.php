<?php
ob_start();
session_start();

// Check if session variables are set
if (!isset($_SESSION['partner_code']) || !isset($_SESSION['email'])) {
    // Redirect to login if session variables are not set
    header('Location: index.php');
    exit();
}

$p_code = 200; // Example value
// Check if the user is eligible for the reward
if (!isset($_SESSION['reward_eligible']) || $_SESSION['reward_eligible'] !== true) {
    // Deny access if not eligible
    die( "<div id='message' style='
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
            Access denied. You must earn the reward first!
        </div>
        <script>
        setTimeout(() => {
         window.location.href = 'home.php';
        }, 15000);
        </script>");



}

// Clear the eligibility flag after use to prevent reuse
unset($_SESSION['reward_eligible']);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reward Effect</title>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="15;url=/dev/<?php echo $p_code; ?>/home.php">
</head>
<body>
<div class="container text-center mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div id="message" class="p-3 border" style="
                color: black;
                font-weight: bold;
                font-size: 20px;
                background-color: #baf61a;
                border-color: #6e7e42;">
                Deine Treue hat sich ausgezahlt! Du hast dir einen Döner oder etwas Gleichwertiges verdient!
            </div>

            <div class="container text-center" style="margin-top: 2px;">
            <h1> <span id="countdown">15</span> seconds.</h1>
           </div>
            
            <!-- Video Section -->
            <div class="mt-4">
                <video id="rewardVideo" class="img-fluid" autoplay muted loop>
                    <source src="combined-output.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div id="videoError" style="display: none; color: red;">
                    Video konnte nicht geladen werden.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const video = document.getElementById('rewardVideo');
    const videoError = document.getElementById('videoError');

    // Autoplay Video Muted, then Unmute Programmatically
    const enableAutoplay = async () => {
        try {
            await video.play();
            video.muted = false; // Unmute after playback starts
        } catch (error) {
            console.warn("Autoplay failed due to browser restrictions:", error);
        }
    };

    // Retry Playback on Page Load
    window.addEventListener('load', () => {
        enableAutoplay();
    });

    // Error Handling for Video
    video.addEventListener('error', () => {
        videoError.style.display = 'block';
    });
</script>



 <!--countdown-->
  <script>
        // Countdown timer (in seconds)
        let countdownTime = 15;

// Function to update the countdown
function updateCountdown() {
    // Decrement the countdown
    countdownTime--;

    // Update the countdown display
    document.getElementById("countdown").textContent = countdownTime;

    // If countdown reaches 0, redirect to home.php
    // if (countdownTime <= 0) {
    //     window.location.href = "/dev/100/home.php";
    // }
}

// Update the countdown every second
setInterval(updateCountdown, 1000);

// Optional: Ensure redirect happens after 15 seconds if the user doesn't interact
setTimeout(function() {
    window.location.href = 'home.php';
}, 15000); // Redirect after 15 seconds
</script>

</body>
</html>

