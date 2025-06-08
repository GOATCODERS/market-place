<?php
// Start output buffering as the very first operation
ob_start();

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection - Fixed path
require_once __DIR__ . '/../includes/database/connection.php';

// Get export format
$format = isset($_GET['format']) ? strtolower($_GET['format']) : '';

// Get filter parameters from URL
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$conditionFilter = isset($_GET['condition']) ? $_GET['condition'] : '';
$priceMin = isset($_GET['price_min']) ? (float) $_GET['price_min'] : '';
$priceMax = isset($_GET['price_max']) ? (float) $_GET['price_max'] : '';

// Build query
$listingQuery = "SELECT l.id, l.item_name, l.description, l.category, l.item_condition, 
                l.price, l.created_at, u.name, u.surname, u.email 
                FROM listings l 
                LEFT JOIN Users u ON l.user_id = u.id 
                WHERE 1=1";

$queryParams = [];
$types = "";

if (!empty($searchTerm)) {
    $searchParam = "%$searchTerm%";
    $listingQuery .= " AND (l.item_name LIKE ? OR l.description LIKE ?)";
    array_push($queryParams, $searchParam, $searchParam);
    $types .= "ss";
}

if (!empty($categoryFilter)) {
    $listingQuery .= " AND l.category = ?";
    array_push($queryParams, $categoryFilter);
    $types .= "s";
}

if (!empty($conditionFilter)) {
    $listingQuery .= " AND l.item_condition = ?";
    array_push($queryParams, $conditionFilter);
    $types .= "s";
}

if ($priceMin !== '') {
    $listingQuery .= " AND l.price >= ?";
    array_push($queryParams, $priceMin);
    $types .= "d";
}

if ($priceMax !== '') {
    $listingQuery .= " AND l.price <= ?";
    array_push($queryParams, $priceMax);
    $types .= "d";
}

$listingQuery .= " ORDER BY l.created_at DESC";

// Get listings
$listingStmt = $conn->prepare($listingQuery);
if (!empty($types) && !empty($queryParams)) {
    $listingStmt->bind_param($types, ...$queryParams);
}
$listingStmt->execute();
$listingsResult = $listingStmt->get_result();
$listings = [];
while ($row = $listingsResult->fetch_assoc()) {
    $listings[] = $row;
}
$listingStmt->close();

// Export based on format
if ($format === 'csv') {
    // Clean any previous output completely
    ob_end_clean();

    // Start a new buffer just in case
    ob_start();

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="listings_export_' . date('Y-m-d') . '.csv"');
    // Add BOM to fix Excel compatibility with UTF-8
    echo "\xEF\xBB\xBF";

    // Create a file pointer
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, ['ID', 'Item Name', 'Category', 'Condition', 'Price', 'Seller Name', 'Seller Email', 'Listed Date', 'Description']);

    // Output each row of data
    foreach ($listings as $listing) {
        $sellerName = isset($listing['name']) ? $listing['name'] . ' ' . $listing['surname'] : 'Unknown';
        $sellerEmail = isset($listing['email']) ? $listing['email'] : 'Unknown';
        // Format price with 2 decimal places
        $formattedPrice = number_format((float) $listing['price'], 2, '.', '');

        fputcsv($output, [
            $listing['id'],
            $listing['item_name'],
            $listing['category'],
            $listing['item_condition'],
            $formattedPrice,
            $sellerName,
            $sellerEmail,
            date('Y-m-d', strtotime($listing['created_at'])),
            $listing['description']
        ]);
    }

    fclose($output);
    exit;

} elseif ($format === 'pdf') {
    // Check for TCPDF in various locations
    $tcpdfFound = true;
    $tcpdfPaths = [
        'vendor/tcpdf/tcpdf.php',
        __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php',
        __DIR__ . '/../vendor/tcpdf/tcpdf.php',
        __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php',
        __DIR__ . '/vendor/tcpdf/tcpdf.php'
    ];

    foreach ($tcpdfPaths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $tcpdfFound = true;
            break;
        }
    }

    // If TCPDF is not found
    // if (true) {
    //     // Clean any previous output completely
    //     ob_end_clean();
    //     // Start new buffer
    //     ob_start();

    //     echo "Error: TCPDF library not found. Please install it using Composer or place it in the correct directory.";
    //     echo "<p><a href='javascript:history.back()'>Go back</a></p>";
    //     exit;
    // }

    // Clean any previous output completely
    ob_end_clean();

    // Start new buffer just in case
    ob_start();

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Marketplace Admin');
    $pdf->SetAuthor('Marketplace Admin');
    $pdf->SetTitle('Listings Export');
    $pdf->SetSubject('Listings Export');

    // Set default header data
    $pdf->SetHeaderData('', 0, 'Listings Export', 'Generated on ' . date('Y-m-d H:i:s'));

    // Set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Add a page
    $pdf->AddPage();

    // Create the table header
    $html = '<h1>Listings Export</h1>';

    // Add filters information if any
    $filtersApplied = [];
    if (!empty($searchTerm))
        $filtersApplied[] = "Search: " . htmlspecialchars($searchTerm);
    if (!empty($categoryFilter))
        $filtersApplied[] = "Category: " . htmlspecialchars($categoryFilter);
    if (!empty($conditionFilter))
        $filtersApplied[] = "Condition: " . htmlspecialchars($conditionFilter);
    if ($priceMin !== '')
        $filtersApplied[] = "Min Price: R" . number_format($priceMin, 2);
    if ($priceMax !== '')
        $filtersApplied[] = "Max Price: R" . number_format($priceMax, 2);

    if (!empty($filtersApplied)) {
        $html .= '<p><strong>Filters applied:</strong> ' . implode(', ', $filtersApplied) . '</p>';
    }

    $html .= '<table border="1" cellpadding="5">
                <thead>
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Condition</th>
                        <th>Price</th>
                        <th>Seller</th>
                        <th>Listed Date</th>
                    </tr>
                </thead>
                <tbody>';

    // Add data rows
    foreach ($listings as $listing) {
        $sellerName = isset($listing['name']) ? htmlspecialchars($listing['name'] . ' ' . $listing['surname']) : 'Unknown';
        $sellerEmail = isset($listing['email']) ? htmlspecialchars($listing['email']) : '';

        $html .= '<tr>
                    <td>' . $listing['id'] . '</td>
                    <td>' . htmlspecialchars($listing['item_name']) . '</td>
                    <td>' . htmlspecialchars($listing['category']) . '</td>
                    <td>' . htmlspecialchars($listing['item_condition']) . '</td>
                    <td>R' . number_format($listing['price'], 2) . '</td>
                    <td>' . $sellerName . '<br><small>' . $sellerEmail . '</small></td>
                    <td>' . date('M d, Y', strtotime($listing['created_at'])) . '</td>
                  </tr>';
    }

    $html .= '</tbody></table>';

    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Close and output PDF document
    $pdf->Output('listings_export_' . date('Y-m-d') . '.pdf', 'D');
    exit;

} else {
    // Normal HTML output - keep the buffer going
    // If no format specified or invalid format, show the export options form
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Export Listings</title>
        <link rel="stylesheet" href="css/styles.css">
        <style>
            .export-container {
                max-width: 600px;
                margin: 50px auto;
                padding: 30px;
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            }

            h1 {
                text-align: center;
                margin-bottom: 30px;
                color: #333;
            }

            .export-options {
                display: flex;
                justify-content: space-around;
                margin-top: 30px;
            }

            .export-option {
                flex: 1;
                max-width: 200px;
                padding: 20px;
                text-align: center;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                margin: 0 10px;
                transition: all 0.3s ease;
            }

            .export-option:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }

            .export-icon {
                font-size: 48px;
                margin-bottom: 15px;
            }

            .pdf-icon {
                color: #e74c3c;
            }

            .csv-icon {
                color: #27ae60;
            }

            .export-btn {
                display: inline-block;
                margin-top: 15px;
                padding: 10px 20px;
                background-color: #3498db;
                color: white;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                transition: background-color 0.3s ease;
            }

            .export-btn:hover {
                background-color: #2980b9;
            }

            .filter-summary {
                background-color: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }

            .filter-summary p {
                margin: 5px 0;
            }

            .back-link {
                display: block;
                text-align: center;
                margin-top: 20px;
                color: #3498db;
                text-decoration: none;
            }

            .back-link:hover {
                text-decoration: underline;
            }

            .warning-message {
                background-color: #fff3cd;
                color: #856404;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
                border-left: 5px solid #ffeeba;
            }
        </style>
    </head>

    <body>
        <div class="export-container">
            <h1>Export Listings</h1>
<!-- 
            <?php if (!class_exists('TCPDF')): ?>
                <div class="warning-message">
                    <strong>Note:</strong> PDF export functionality requires the TCPDF library, which is not currently
                    installed.
                    Only CSV export will be available until TCPDF is installed.
                </div>
            <?php endif; ?> -->

            <?php if (!empty($searchTerm) || !empty($categoryFilter) || !empty($conditionFilter) || $priceMin !== '' || $priceMax !== ''): ?>
                <div class="filter-summary">
                    <h3>Current Filters</h3>
                    <?php if (!empty($searchTerm)): ?>
                        <p><strong>Search:</strong> <?php echo htmlspecialchars($searchTerm); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($categoryFilter)): ?>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($categoryFilter); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($conditionFilter)): ?>
                        <p><strong>Condition:</strong> <?php echo htmlspecialchars($conditionFilter); ?></p>
                    <?php endif; ?>

                    <?php if ($priceMin !== ''): ?>
                        <p><strong>Min Price:</strong> R<?php echo number_format($priceMin, 2); ?></p>
                    <?php endif; ?>

                    <?php if ($priceMax !== ''): ?>
                        <p><strong>Max Price:</strong> R<?php echo number_format($priceMax, 2); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <p>Total listings found: <strong><?php echo count($listings); ?></strong></p>
            <p>Select the format to export the listings:</p>

            <div class="export-options">
                <?php if (true): ?>
                    <div class="export-option">
                        <div class="export-icon pdf-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h3>PDF</h3>
                        <p>Export as a formatted PDF document</p>
                        <a href="export_listings.php?format=pdf<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . urlencode($categoryFilter) : ''; ?><?php echo !empty($conditionFilter) ? '&condition=' . urlencode($conditionFilter) : ''; ?><?php echo $priceMin !== '' ? '&price_min=' . $priceMin : ''; ?><?php echo $priceMax !== '' ? '&price_max=' . $priceMax : ''; ?>"
                            class="export-btn">Export as PDF</a>
                    </div>
                <?php else: ?>
                    <div class="export-option" style="opacity: 0.5;">
                        <div class="export-icon pdf-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h3>PDF</h3>
                        <p>TCPDF library needed</p>
                        <span class="export-btn" style="background-color: #ccc; cursor: not-allowed;">Export as PDF</span>
                    </div>
                <?php endif; ?>

                <div class="export-option">
                    <div class="export-icon csv-icon">
                        <i class="fas fa-file-csv"></i>
                    </div>
                    <h3>CSV</h3>
                    <p>Export as a CSV spreadsheet</p>
                    <a href="export_listings.php?format=csv<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . urlencode($categoryFilter) : ''; ?><?php echo !empty($conditionFilter) ? '&condition=' . urlencode($conditionFilter) : ''; ?><?php echo $priceMin !== '' ? '&price_min=' . $priceMin : ''; ?><?php echo $priceMax !== '' ? '&price_max=' . $priceMax : ''; ?>"
                        class="export-btn">Export as CSV</a>
                </div>
            </div>

            <a href="admin_dashboard.php?page=listings<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . urlencode($categoryFilter) : ''; ?><?php echo !empty($conditionFilter) ? '&condition=' . urlencode($conditionFilter) : ''; ?><?php echo $priceMin !== '' ? '&price_min=' . $priceMin : ''; ?><?php echo $priceMax !== '' ? '&price_max=' . $priceMax : ''; ?>"
                class="back-link">← Back to Listings</a>
        </div>

        <!-- Include Font Awesome for icons -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    </body>

    </html>
    <?php
}
?>