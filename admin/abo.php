<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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
if ($_SESSION['partner_code'] != $partner_code_url )
{
        // If any condition fails, destroy the session and redirect
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
}

if (
    !isset($_SESSION['user_email']) ||  // Ensure user_email is set
    !isset($_SESSION['valid']) ||      // Ensure valid is set
    $_SESSION['valid'] !== true ||     // Check if valid is true
    !isset($_SESSION['timeout']) ||    // Ensure timeout is set
    $_SESSION['timeout'] < time()      // Check if session has expired
) {
    // If any condition fails, destroy the session and redirect
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

///// db querys ////////////
require_once "../db-connect.php";
require_once "Classes/Database.php"; 
require_once "Classes/CardOrder.php"; 

$db = new Database($servername, $dbname, $username, $password);

//$partnerCode = 123; // Replace with the actual partner code
$partnerInfo = $db->getPartnerInfo($partner_code);

if ($partnerInfo) {
    // Data retrieved successfully
    $companyName = $partnerInfo['company_name'];
    $street = $partnerInfo['street'];
    $number = $partnerInfo['number'];
    $zip = $partnerInfo['zip'];
    $state = $partnerInfo['state'];
    $tel = $partnerInfo['Tel'];
    $payedUntil = $partnerInfo['payed_until'];
    $payedAt = $partnerInfo['payed_at'];
    $email = $partnerInfo['email'];
    $active = $partnerInfo['active'];
    $isActive = ($active === 'true');
    $klientSeit = $partnerInfo['klient_seit'];
} else {
    // No data found
    echo "No partner information found for the given partner code.";
}

// erster kartnenummer &  letzer kartennunner finden
// Example usage
$db = new Database($servername, $dbname, $username, $password);


$cardRange = $db->getUserIdRangeByPartnerCode($partner_code);

if ($cardRange) {
     $firstUserId = $cardRange['first_user_id'] ;
     $lastUserId =  $cardRange['last_user_id'] ;
} else {
    echo "No data found for partner_code: $partner_code";
}

// card command
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $clientEmail = htmlspecialchars($_POST['client_email']);
        // Initialize the CardOrder object
        $order = new CardOrder("zenasni55@yahoo.fr", $clientEmail); // Replace with your admin email

        // Set the form data
        $order->setPartnerCode( $partner_code);
        $order->setCardNumber($_POST['cardNumber']);
        $order->setMessage($_POST['message'] ?? '');
        $order->setClientEmail($clientEmail);


        // Validate and send the email
        $order->validate();
        $order->sendEmail();

        $successMessage = "Bestellung erfolgreich versendet! \n wir werden sie bald kontaktieren!";
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
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

    <title>SB Admin 2 - Other Utilities</title>

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

                <!-- Topbar -->

<?php require_once "top_bar_menu.php"; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <!-- <h1 class="h3 mb-1 text-gray-800">Other Utilities</h1>
                    <p class="mb-4">Bootstrap's default utility classes can be found on the official <a
                            href="https://getbootstrap.com/docs">Bootstrap Documentation</a> page. The custom utilities
                        below were created to extend this theme past the default utility classes built into Bootstrap's
                        framework.</p> -->

                    <!-- Content Row -->
                    <div class="row">

                        <div class="col-lg-6">

                            <!-- Overflow Hidden -->
                            <div class="card mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Abo Daten</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Partner seit:</strong> <?= htmlspecialchars($klientSeit ?? "N/A"); ?></li>
                                    <li class="list-group-item"><strong>Abo Beginn:</strong> <?= htmlspecialchars($payedAt ?? "N/A"); ?></li>
                                    <li class="list-group-item"><strong>Abo Ende:</strong> <?= htmlspecialchars($payedUntil ?? "N/A"); ?></li>
                                    <li class="list-group-item">
                                        <strong>Abo-status:</strong><?= $isActive ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>'; ?><!-- Change to text-danger if inactive -->
                                    </li>
                                   
                                    <li class="list-group-item"><strong>Erste Kartennummer:</strong> <?= htmlspecialchars($firstUserId ?? "N/A"); ?></li>
                                    <li class="list-group-item"><strong>Letzte Kartennummer:</strong> <?= htmlspecialchars($lastUserId ?? "N/A"); ?></li>
                               
                                    <li class="list-group-item">
                                        <strong>Letzte Rechnung:</strong> 
                                        <a href="yourfile.pdf" class="btn btn-primary btn-sm ms-3" download>
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>


                            <!-- Progress Small -->
                            <!-- <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Progress Small Utility</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-1 small">Normal Progress Bar</div>
                                    <div class="progress mb-4">
                                        <div class="progress-bar" role="progressbar" style="width: 75%"
                                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="mb-1 small">Small Progress Bar</div>
                                    <div class="progress progress-sm mb-2">
                                        <div class="progress-bar" role="progressbar" style="width: 75%"
                                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    Use the <code>.progress-sm</code> class along with <code>.progress</code>
                                </div>
                            </div> -->

                            <!-- Dropdown No Arrow -->
                            <!-- <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Dropdown - No Arrow</h6>
                                </div>
                                <div class="card-body">
                                    <div class="dropdown no-arrow mb-4">
                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            Dropdown (no arrow)
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                    Add the <code>.no-arrow</code> class alongside the <code>.dropdown</code>
                                </div>
                            </div> -->

                        </div>

                        <div class="col-lg-6">

                        <!-- Client Information Card -->
                        <div class="card">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Partner Information</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Firmen Name:</strong>  <?= htmlspecialchars($companyName ?? "N/A"); ?></li>
                                    <li class="list-group-item"><strong>Address:</strong> <?= htmlspecialchars($street ?? "N/A"); ?><?= htmlspecialchars($number ?? "N/A"); ?></li>
                                    <li class="list-group-item"><strong>PLZ:</strong>  <?= htmlspecialchars($zip ?? "N/A"); ?></li>
                                    <li class="list-group-item"><strong>Stadt:</strong> <?= htmlspecialchars($state ?? "N/A"); ?></li>
                                    <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($tel ?? "N/A"); ?></li>

                                    <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($email ?? "N/A"); ?></li>
                                </ul>
                            </div>
                        </div>

                        </div>

                    
                        <div class="col-lg-6">

                        <!-- Client Information Card -->
                        <div class="card">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Bonuskarten bestellen</h6>
                            </div>
                            <div class="card-body">
                                        <!-- Display success or error message -->
                                    <?php if (isset($successMessage)): ?>
                                        <div class="alert alert-success text-center"><?= $successMessage ?></div>
                                    <?php elseif (isset($errorMessage)): ?>
                                        <div class="alert alert-danger text-center"><?= $errorMessage ?></div>
                                    <?php endif; ?>

                                    <!-- Form -->
                                                    <!-- Form -->
                        <form action="" method="POST" class="needs-validation" novalidate>


                            <!-- Cards Dropdown -->
                            <div class="mb-3">
                            <div class="mb-3">
                            <label for="client_email" class="form-label">E-Mail:</label>
                            <input type="email" id="client_email" name="client_email" class="form-control" required >
                            </div>
                                <label for="cards" class="form-label">Anzahl der Karten:</label>
                                <select id="cards" name="cardNumber" class="form-select" required>
                                    
                                    <option value="50" selected >50 Karten</option>
                                    <option value="100">100 Karten</option>
                                    <option value="200">200 Karten</option>
                                </select>
                                <div class="invalid-feedback">Bitte wählen Sie eine Kartenanzahl aus.</div>
                            </div>

                            <!-- Message Textarea -->
                            <div class="mb-3">
                                <label for="message" class="form-label">Nachricht (optional):</label>
                                <textarea id="message" name="message" rows="4" class="form-control"></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Bestellen</button>
                            </div>
                        </form>

                            </div>
                        </div>

                        </div>


                        <div class="col-lg-6">

                        <!-- Client Year Report -->
                        <div class="card">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Jahresreports</h6>
                            </div>
                            <div class="card-body">

                              
                                                    <!-- Form -->
                        <form action="generate_pdf.php" method="POST" class="needs-validation" novalidate target="_blank">


                            <!-- Cards Dropdown -->
                            <div class="mb-3">
                                <?php $currentYear = date("Y"); // Get the current year ?>

                                <label for="cards" class="form-label">Jahr:</label>
                                <select id="year" name="year" class="form-select" required>
                                    
                                    <option value="<?php echo $currentYear;?>"><?php echo $currentYear;?></option>
                                    <option value="<?php echo $currentYear - 1;?>"><?php echo $currentYear -1 ;?></option>
                                    <option value="<?php echo $currentYear - 2 ;?>"><?php echo $currentYear -2 ;?></option>
                                </select>
                                <div class="invalid-feedback">Bitte wählen Sie ein Jahr aus.</div>
                            </div>


                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Erstellen</button>
                            </div>
                        </form>

                            </div>
                        </div>

                        </div>
                       


                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
<?php  require_once "footer.php"; ?> 
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

</body>

</html>