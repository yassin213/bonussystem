<?php
ob_start(); // Start output buffering
session_start();
header('Content-Type: text/html; charset=utf-8');

// Get the full URL path from the REQUEST_URI
$urlPath = $_SERVER['REQUEST_URI'];
// Use regex to capture the number (200) in the URL path
if (preg_match("#/dev/(\d+)/admin/#", $urlPath, $matches)) {
    $partner_code_url = $matches[1];
} else {
    echo "No Partner code match found";
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

if (
    !isset($_SESSION['user_email']) ||  // Ensure user_email is set
    !isset($_SESSION['valid']) ||      // Ensure valid is set
    $_SESSION['valid'] !== true ||     // Check if valid is true
    !isset($_SESSION['timeout'])  ||
    !isset($_SESSION['partner_code'])  // Ensure timeout is set
) {
    // If any condition fails, destroy the session and redirect
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Validate the form submission
if (!isset($_POST['year']) || !is_numeric($_POST['year'])) {
    die("Ungültige Jahresauswahl.");
}

// Get the selected year
$selectedYear = intval($_POST['year']);

// Include the FPDF library
require_once "fpdf/fpdf.php";
require_once "../db-connect.php";  // Ensure your database connection variables are correct

// Database connection
$pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch the report data based on the selected year
$query = "
    SELECT * 
    FROM user_bonus 
    WHERE reward != 0 
      AND partner_code = :partner_code 
      AND SUBSTRING_INDEX(reward_year, '.', -1) = :year
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':partner_code', $_SESSION['partner_code'], PDO::PARAM_INT);
$stmt->bindParam(':year', $selectedYear, PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch partner company info
$partnerQuery = "SELECT * FROM partner_info WHERE partner_code = :partner_code";
$partnerStmt = $pdo->prepare($partnerQuery);
$partnerStmt->bindParam(':partner_code', $_SESSION['partner_code'], PDO::PARAM_INT);
$partnerStmt->execute();
$partnerInfo = $partnerStmt->fetch(PDO::FETCH_ASSOC);

// Create a new PDF document
$pdf = new FPDF();
$pdf->AddPage();


// Add logo image at the top-left
$pdf->Image('img/logo_small.png', 10, 10, 30); // Path, X position, Y positi
// Add company information (name, address, etc.)
//$pdf->SetY(20); // Adjust 20 to your desired starting position
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(200, 10, 'COMEBACK24', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(200, 6, 'Tübinger Str 82 72760 Reutlingen', 0, 1, 'C');
$pdf->Cell(200, 6, 'Tel: +1 234 567 890', 0, 1, 'C');
$pdf->Cell(200, 6, 'support@comeback24.de', 0, 1, 'C');
$pdf->Cell(200, 6, 'www.comeback24.de', 0, 1, 'C');
$pdf->Ln(10); // Line break with smaller space (default is 10, now 2)

// Add Partner company information (dynamically fetched)
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(200, 6, $partnerInfo['company_name'], 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(200, 6,  $partnerInfo['street'] . ' ' . $partnerInfo['number'] . ', ' . $partnerInfo['zip'] . ' ' . $partnerInfo['state'], 0, 1, 'L');
$pdf->Cell(200, 6, 'Phone: ' . $partnerInfo['Tel'], 0, 1, 'L');
$pdf->Cell(200, 6, 'Email: ' . $partnerInfo['email'], 0, 1, 'L');
$pdf->Cell(200, 6, 'Partner Code: ' . $partnerInfo['partner_code'], 0, 1, 'L');
$pdf->Ln(10); // Line break

// Add report title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(200, 10, 'Annual Report - ' . $selectedYear, 0, 1, 'C');
$pdf->Ln(10); // Line break

// Add current date to the report in EU format (dd.mm.yyyy)
$pdf->SetFont('Arial', 'I', 12);  // Italic font for date
$pdf->Cell(200, 10, 'Report generated on: ' . date('d.m.Y'), 0, 1, 'C');
$pdf->Ln(10); // Line break


// Add table headers
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, 'User ID', 1);
$pdf->Cell(40, 10, 'scan time', 1);
// $pdf->Cell(40, 10, 'Points', 1);
$pdf->Cell(40, 10, 'Reward time', 1);
$pdf->Cell(90, 10, 'Reward Year', 1);  // Increased width here
$pdf->Ln();

// Add table content if rewards exist
$totalReward = 0;
if (empty($data)) {
    // If no rewards are found, display a message
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(200, 10, 'Keine Belohnung für das Jahr ' . $selectedYear, 0, 1, 'C');
} else {
    foreach ($data as $row) {
        $pdf->Cell(40, 10, htmlspecialchars($row['user_id']), 1);
        $pdf->Cell(40, 10, htmlspecialchars($row['timestamp']), 1);
        // $pdf->Cell(40, 10, htmlspecialchars($row['point']), 1);
        $pdf->Cell(40, 10, htmlspecialchars($row['reward']), 1);
        $pdf->Cell(90, 10, htmlspecialchars($row['reward_year']), 1);  // Increased width here
        $pdf->Ln();

        $totalReward += (float)$row['reward'];
    }

    // Add total reward
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(120, 10, 'Totale Belohnungen im Jahr ' . $selectedYear . ': ', 0, 0, 'R');
    $pdf->Cell(40, 10, intval($totalReward), 0, 1, 'L');
}

// Add page number in footer (only after the first page)
$pdf->AliasNbPages(); // This will add the {nb} placeholder for total pages
$pdf->SetY(-15);  // Position at the bottom
$pdf->SetFont('Arial', 'I', 8);

// Footer method to print page number
if ($pdf->PageNo() > 1) {
    $pdf->Cell(0, 10, 'Seite ' . $pdf->PageNo() . ' von {nb}', 0, 0, 'C');
}

// Output PDF
$pdf->Output('D', 'year_report_' . $selectedYear . '.pdf');
?>
