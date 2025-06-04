<?php
require_once 'Classes/Database.php'; // Assuming the Database class is in this file
require_once "db-connect.php";

try {
    // Initialize Database class
    $db = new Database($servername, $dbname, $username, $password);

    // Get user ID and partner code from the URL
     $user_id = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? $_GET['user_id'] : null;
     $partner_code = isset($_GET['partner_code']) && is_numeric($_GET['partner_code']) ? $_GET['partner_code'] : null;
    

    if ($user_id === null || $partner_code === null ) {
        //echo "<h1>Error: Missing User ID or Partner Code in the URL.</h1>";
        //$backUrl = "/dev/$directory/show_points.php?user_id=$user_id&partner_code=$partner_code";
        
        echo '<div class="alert alert-danger text-center" role="alert">';
        echo '<h1 style="color: white; font-size: 36px; font-weight: bold; padding: 20px; background-color: #f44336; border-radius: 5px;">Error: Missing User ID or Partner Code in the URL.</h1>';
        echo '<a href="/" class="btn btn-primary btn-lg mt-3">Back to Points Page</a>';
        echo '</div>';
        exit();
         
    }
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get PIN from the form
         $pin = isset($_POST['pin']) && filter_var($_POST['pin'], FILTER_VALIDATE_INT) ? $_POST['pin'] : null;

        if ($pin === null) {
            // Define the directory dynamically (e.g., partner code)
            //$directory = $partner_code;
        
            // Build the dynamic URL using user_id and partner_code
            //$backUrl = "/dev/$directory/show_points.php?user_id=$user_id&partner_code=$partner_code";
        
            echo '<div class="alert alert-danger text-center" role="alert">';
            echo '<h1 style="color: white; font-size: 36px; font-weight: bold; padding: 20px; background-color: #f44336; border-radius: 5px;">Error: Invalid PIN. Only numeric values are allowed.</h1>';
            echo '<a href="/" class="btn btn-primary btn-lg mt-3">Back to Points Page</a>';
            echo '</div>';
            exit();
        }

        // Validate the PIN using the Database class
        $result = $db->validatePin($user_id, $partner_code, $pin);

        if ($result) {
            $points = $result['point'];
            $image_path = $db->getPointsImagePath($points);

            echo '<!DOCTYPE html>';
            echo '<html lang="de">';
            echo '<head>';
            echo '    <meta charset="UTF-8">';
            echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
            echo '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">';
            echo '    <title>Show my Points</title>';
            echo '</head>';
            echo '<body>';
            echo '    <div class="container text-center" style="margin-top:10px; margin-bottom:10px;">';

            if (file_exists($image_path)) {
                echo "        <img src='$image_path' class='img-fluid rounded mx-auto d-block' alt='Points Image'>";
            } else {
                echo "        <p>You now have $points points, but no image found.</p>";
            }

            echo '    </div>';
            echo '</body>';
            echo '</html>';

            // Redirect after 30 seconds
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'https://comeback24.de';
                    }, 300000);
                  </script>";
            exit();
        } else {
            echo '<div class="alert alert-danger text-center" role="alert">';
            echo '<h1 style="color: white; font-size: 36px; font-weight: bold; padding: 20px; background-color: #f44336; border-radius: 5px;">Error: Invalid PIN or no record found for the provided User ID and Partner Code.</h1>';
            echo '<a href="/" class="btn btn-primary btn-lg mt-3">Back to Points Page</a>';
            echo '</div>';
            exit();
        }
    } else {
        // Show the form for PIN input
        echo '<!DOCTYPE html>';
        echo '<html lang="de">';
        echo '<head>';
        echo '    <meta charset="UTF-8">';
        echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '    <title>Enter PIN</title>';
        echo '</head>';
        echo '<body>';
        echo '    <div class="container mt-5">';
        echo '        <h1 class="text-center">Geben Sie die 4-stellige PIN auf der Rückseite Ihrer Bonuskarte ein.</h1>';
        echo '        <form method="POST" class="mt-4">';
        echo '            <div class="mb-3">';
        echo '                <label for="pin" class="form-label">PIN</label>';
        echo '                <input type="password" class="form-control form-control-lg rounded" id="pin" name="pin" required>';
        echo '            </div>';
        echo '            <button type="submit" class="btn btn-primary btn-lg w-100">Submit</button>';
        echo '        </form>';
        echo '    </div>';
        echo '</body>';
        echo '</html>';
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
