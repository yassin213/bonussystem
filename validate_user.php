<?php
// Database connection details
$servername = "sql686.your-server.de";
$username = "comeba_1";
$password = "py69fFcMjpwQKv3h";
$dbname = "comeba_db1";

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get user ID and partner code from URL and validate as integers
    $user_id = isset($_GET['user_id']) && filter_var($_GET['user_id'], FILTER_VALIDATE_INT) ? $_GET['user_id'] : null;
    $partner_code = isset($_GET['partner_code']) && filter_var($_GET['partner_code'], FILTER_VALIDATE_INT) ? $_GET['partner_code'] : null;

    // Check if user_id and partner_code are provided
    if ($user_id === null || $partner_code === null) {
        die("Error: Invalid User ID or Partner Code. Only numeric values are allowed.");
    }

    // Check if both user_id and partner_code exist in the database
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user_bonus WHERE user_id = :user_id AND partner_code = :partner_code");
    $stmt->execute(['user_id' => $user_id, 'partner_code' => $partner_code]);
    $exists = $stmt->fetchColumn();

    // If user and partner do not exist, display an error and stop execution
    if (!$exists) {
        die("Error: Invalid User ID or Partner Code. No record found.");
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
