<?php
// Start output buffering to prevent any unwanted output
ob_start();

// Include database connection
require_once __DIR__ . '/../includes/database/connection.php';

// Get export format
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

// Get search filters (same as in admin_users.php)
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

// Build query
$userQuery = "SELECT * FROM Users WHERE 1=1";
$queryParams = [];
$types = "";

if (!empty($searchTerm)) {
    $searchParam = "%$searchTerm%";
    $userQuery .= " AND (name LIKE ? OR surname LIKE ? OR email LIKE ?)";
    array_push($queryParams, $searchParam, $searchParam, $searchParam);
    $types .= "sss";
}

if (!empty($roleFilter)) {
    $userQuery .= " AND role = ?";
    array_push($queryParams, $roleFilter);
    $types .= "s";
}

$userQuery .= " ORDER BY created_at DESC";

// Get users
$userStmt = $conn->prepare($userQuery);
if (!empty($types) && !empty($queryParams)) {
    $userStmt->bind_param($types, ...$queryParams);
}
$userStmt->execute();
$usersResult = $userStmt->get_result();
$users = [];
while ($row = $usersResult->fetch_assoc()) {
    $users[] = $row;
}
$userStmt->close();

// Set filename
$filename = 'users_export_' . date('Y-m-d');

// Clear any output that might have been generated so far
ob_end_clean();

// Export based on format
if ($format === 'pdf') {
    try {
        exportPDF($users, $filename);
    } catch (Exception $e) {
        // If an error occurs, redirect to the admin page with an error message
        header('Location: admin_dashboard.php?page=users&error=pdf_generation_failed');
        exit;
    }
} elseif ($format === 'docx') {
    try {
        exportDOCX($users, $filename);
    } catch (Exception $e) {
        // If an error occurs, redirect to the admin page with an error message
        header('Location: admin_dashboard.php?page=users&error=docx_generation_failed');
        exit;
    }
} else {
    // Invalid format
    header('Location: admin_dashboard.php?page=users&error=invalid_format');
    exit;
}

/**
 * Export users data as PDF
 */
function exportPDF($users, $filename)
{
    // Require TCPDF library
    require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Disable any default headers/footers
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set document information
    $pdf->SetCreator('Marketplace Admin');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Users Report');
    $pdf->SetSubject('Users List');
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(true, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Add title
    $pdf->Cell(0, 10, 'Users List', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 5, 'Generated on ' . date('Y-m-d H:i:s'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Set font for table
    $pdf->SetFont('helvetica', '', 10);

    // Create table header
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
    $pdf->Cell(50, 7, 'Name', 1, 0, 'C', true);
    $pdf->Cell(70, 7, 'Email', 1, 0, 'C', true);
    $pdf->Cell(25, 7, 'Role', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Joined Date', 1, 1, 'C', true);
    
    // Create table rows
    $pdf->SetFont('helvetica', '', 9);
    foreach ($users as $user) {
        $pdf->Cell(15, 6, $user['id'], 1, 0, 'C');
        $pdf->Cell(50, 6, $user['name'] . ' ' . $user['surname'], 1, 0, 'L');
        $pdf->Cell(70, 6, $user['email'], 1, 0, 'L');
        $pdf->Cell(25, 6, ucfirst($user['role']), 1, 0, 'L');
        $pdf->Cell(30, 6, date('M d, Y', strtotime($user['created_at'])), 1, 1, 'C');
    }
    
    // Close and output PDF document
    $pdf->Output($filename . '.pdf', 'D');
    exit;
}

/**
 * Export users data as DOCX
 */
function exportDOCX($users, $filename)
{
    // Require PHPWord library (via Composer autoloader)
    require_once __DIR__ . '/../vendor/autoload.php';

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $titleStyle = ['bold' => true, 'size' => 16];
    $headerStyle = ['bold' => true, 'size' => 12];
    $cellStyle = ['size' => 10];

    $section = $phpWord->addSection();
    $section->addText('Users List', $titleStyle);
    $section->addText('Generated on ' . date('Y-m-d H:i:s'), ['italic' => true, 'size' => 10]);
    $section->addTextBreak(1);

    $table = $section->addTable([
        'borderSize' => 1,
        'borderColor' => '000000',
        'cellMargin' => 80
    ]);

    $table->addRow();
    $table->addCell(800)->addText('ID', $headerStyle);
    $table->addCell(2000)->addText('Name', $headerStyle);
    $table->addCell(3000)->addText('Email', $headerStyle);
    $table->addCell(1500)->addText('Role', $headerStyle);
    $table->addCell(1500)->addText('Joined Date', $headerStyle);

    foreach ($users as $user) {
        $table->addRow();
        $table->addCell(800)->addText($user['id'], $cellStyle);
        $table->addCell(2000)->addText($user['name'] . ' ' . $user['surname'], $cellStyle);
        $table->addCell(3000)->addText($user['email'], $cellStyle);
        $table->addCell(1500)->addText(ucfirst($user['role']), $cellStyle);
        $table->addCell(1500)->addText(date('M d, Y', strtotime($user['created_at'])), $cellStyle);
    }

    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment;filename="' . $filename . '.docx"');
    header('Cache-Control: max-age=0');

    $objWriter->save('php://output');
    exit;
}
?>