<?php
session_start();

// Prevent caching
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");

// Get the full URL path from the REQUEST_URI
$urlPath = $_SERVER['REQUEST_URI'];
// Use regex to capture the number (200) in the URL path
if (preg_match("#/dev/(\d+)/admin/#", $urlPath, $matches)) {
    $partner_code_url = $matches[1];
} else {
    echo "No Partner_code match found.index";
    exit;
}

if (isset($_SESSION['partner_code']) && filter_var($_SESSION['partner_code'], FILTER_VALIDATE_INT)) {
    $partner_code = htmlspecialchars($_SESSION['partner_code']);
} else {
    // Destroy session if partner_code is invalid
    session_destroy();
    header("Location: login.php");
    exit;
}
// if ($_SESSION['partner_code'] != $partner_code_url )
// {
//         // If any condition fails, destroy the session and redirect
//         session_unset();
//         session_destroy();
//         header("Location: login.php");
//         exit;
// }

if (
    !isset($_SESSION['user_email']) ||  // Ensure user_email is set
    !isset($_SESSION['valid']) ||      // Ensure valid is set
    $_SESSION['valid'] !== true ||     // Check if valid is true
    !isset($_SESSION['timeout'])   // Ensure timeout is set
     /* $_SESSION['timeout'] < time()       // Check if session has expired */
) {
    // If any condition fails, destroy the session and redirect
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Validate and sanitize the partner_code session variable
 

$currentDate = date('Y-m-d'); // Get today's date in 'YYYY-MM-DD' format
// Database connection details
require_once "../db-connect.php";
try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
// Query to count users in the user_bonus table with the matching partner_code
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS user_count FROM user_bonus WHERE partner_code = :partner_code");
    $stmt->bindParam(':partner_code', $partner_code, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $userCount = $result['user_count'] ?? 0; // Default to 0 if no rows are found

    if ($userCount > 1) { // Ensure there's at least 2 active users to decrement
        $userCount -= 1; // Subtract 3 from active_users 1800 1799 ids, -3 because the db start from 0 
    } else {
        $userCount = 0; // If less than 2, set it to 0 to avoid negative values
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Query to sum rewards in the user_bonus table with the matching partner_code
try {
    $currentYear = date('Y'); // Get the current year

    // Query to sum rewards in the user_bonus table for the current year and matching partner_code
    $stmt = $pdo->prepare("
        SELECT SUM(LENGTH(reward_year) - LENGTH(REPLACE(reward_year, :current_year, '')))/LENGTH(:current_year) AS count_of_current_year
        FROM user_bonus 
        WHERE partner_code = :partner_code
    ");
    $stmt->bindParam(':partner_code', $partner_code, PDO::PARAM_INT);
    $stmt->bindParam(':current_year', $currentYear, PDO::PARAM_STR); // Treat the year as a string
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $countOfCurrentYear = $result['count_of_current_year'] ?? 0; // Default to 0 if no rows are found or count is NULL
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

?>

<?php
$currentMonth = date('m.Y'); // Example: 12.2024

try {
    // Query to count the number of entries with the current month in reward_year
    $stmt = $pdo->prepare("
        SELECT SUM(
            (LENGTH(reward_year) - LENGTH(REPLACE(reward_year, :current_month, ''))) / LENGTH(:current_month)
        ) AS reward_count
        FROM user_bonus
        WHERE partner_code = :partner_code
    ");
    
    // Bind parameters
    $stmt->bindParam(':partner_code', $partner_code, PDO::PARAM_INT);
    $stmt->bindParam(':current_month', $currentMonth, PDO::PARAM_STR); // Use the current month dynamically
    $stmt->execute();

    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get the count of rewards for the current month
    $rewardCount = $result['reward_count'] ?? 0; // Default to 0 if no matching entries are found
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Now, you can display the reward count
//echo "Reward count for {$currentMonth}: " . $rewardCount;
?>

<?php
try {
            // Query to count the number of users with exactly 10 points for the given partner_code
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS user_count
                FROM user_bonus
                WHERE partner_code = :partner_code AND point = 10
            ");
            
            // Bind the partner_code parameter
            $stmt->bindParam(':partner_code', $partner_code, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the result
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get the count of users
            $userCount_with_10 = $result['user_count'] ?? 0; // Default to 0 if no rows are found

            // Display the count
            //echo "Total users with exactly 10 points: " . $userCount;
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
?>
<?php
try {
            // Get the count of users that have not 0 point active users 
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS user_count
                FROM user_bonus
                WHERE partner_code = :partner_code AND (point != 0 OR reward != 0)
            ");
            
            // Bind the partner_code parameter
            $stmt->bindParam(':partner_code', $partner_code, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the result
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get the count of users that have not 0 point
            $active_users = $result['user_count'] ?? 0; // Default to 0 if no rows are found

            // if ($active_users > 1) { // Ensure there's at least 2 active users to decrement
            //     //$active_users -= 2; // Subtract 2 from active_users 1800 1799 ids
            // } else {
            //     $active_users = 0; // If less than 2, set it to 0 to avoid negative values
            // }
            

            // Display the count
            //echo "Total users with exactly 10 points: " . $userCount;
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
?>

<!DOCTYPE html>
<html lang="de">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>CB Admin Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

 <?php require_once "side_bar.php";?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

<?php require_once "top_bar_menu.php"; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
                    </div> -->

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Users</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($userCount) ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <!-- <i class="fas fa-calendar fa-2x text-gray-300"></i> -->
                                            <i class="fas fa-users fa-2x "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Aktive users</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($active_users) ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <!-- <i class="fas fa-calendar fa-2x text-gray-300"></i> -->
                                            <i class="fas fa-address-card fa-2x "></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Gegebene Döner im Jahr <?php echo date("Y");?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars((int)$countOfCurrentYear) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                             
                                            <i class="fas fa-hamburger" style="font-size:24px;"></i>





                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Gegebene Döner in  Monat <?php echo $currentMonth;?>
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"> <?= htmlspecialchars((int)$rewardCount)?></div>
                                                </div>
                                                <div class="col">
                                                    <!-- <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                            style="width: 50%" aria-valuenow="50" aria-valuemin="0"
                                                            aria-valuemax="100"></div>
                                                    </div> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                        <i class="fas fa-hamburger" style="font-size:24px;"></i>
                                       

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Total Users mit 10 Punkte</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars((int)$userCount_with_10)?></div>
                                        </div>
                                        <div class="col-auto">
                                           
                                            <i class="fa fa-battery-full" style="font-size:24px"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Define variables for pagination
                    $limit = 10; // Number of rows per page
                    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1; // Current page number
                    $offset = ($page - 1) * $limit; // Offset for SQL query

                    try {
                        // Get the total number of rows for the specific partner_code
                        $stmt = $pdo->prepare("
                            SELECT COUNT(*) AS total 
                            FROM user_bonus 
                            WHERE partner_code = :partner_code
                        ");
                        $stmt->bindParam(':partner_code', $partner_code, PDO::PARAM_INT);
                        $stmt->execute();
                        $totalRows = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                        // Fetch data with limit and offset for the current page
                        $stmt = $pdo->prepare("
                            SELECT user_id, timestamp, point, reward, reward_year, PIN 
                            FROM user_bonus 
                            WHERE partner_code = :partner_code 
                            ORDER BY timestamp DESC 
                            LIMIT :limit OFFSET :offset
                        ");
                        $stmt->bindParam(':partner_code', $partner_code, PDO::PARAM_INT);
                        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();

                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        die("Database error: " . htmlspecialchars($e->getMessage()));
                    }

                    // Calculate total pages
                    $totalPages = ceil($totalRows / $limit);
                    ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">User Bonus Data</h6>
                                <!-- Button to trigger PDF download -->
 
                          
                    </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>Letzte Scan</th>
                                            <th>Punkte</th>
                                            
                                            <th>Anzahl der Belohnungen</th>
                                            <th>Belohnung Datum</th>
                                            <th>PIN</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if (!empty($rows)): ?>
                                            <?php foreach ($rows as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($row['timestamp']) ?>
                                                        <?php if (substr($row['timestamp'], 0, 10) === $currentDate): ?>
                                                            <i class="fas fa-user-plus text-success ms-2" style="font-size:16px;" title="Scanned Today"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['point']) ?></td>
                                                    <td><?= htmlspecialchars($row['reward']) ?></td>
                                                    <td>
                                                        <?php
                                                        // Get the current date in dd.mm.yyyy format
                                                        $currentDateFormatted = date('d.m.Y');
                                                        $rewardDates = explode(',', $row['reward_year']); // Split reward_year into an array

                                                        // Check if the current date exists in the reward_year field
                                                        if (in_array($currentDateFormatted, $rewardDates)): ?>
                                                            <?= htmlspecialchars($row['reward_year']) ?><i class="fas fa-user-plus text-success ms-2" style="font-size:16px;" title="Scanned Today"></i>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($row['reward_year']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['PIN']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No data available for the specified partner code.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>


                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo; Previous</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = max(1, $page - 5); $i <= min($totalPages, $page + 5); $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                                <span aria-hidden="true">Next &raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>


                </div>
                                <!-- /.container-fluid -->

                </div>
                            <!-- End of Main Content -->

                            <!-- Footer -->
<?php  require_once "footer.php";?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout.php">Logout</a>
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

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

</body>

</html>
