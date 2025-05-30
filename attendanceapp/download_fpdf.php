<?php
// Create fpdf directory if it doesn't exist
if (!file_exists('fpdf')) {
    mkdir('fpdf', 0777, true);
}

// Download FPDF
$url = 'http://www.fpdf.org/en/download/fpdf184.zip';
$zipFile = 'fpdf.zip';
file_put_contents($zipFile, file_get_contents($url));

// Extract the zip file
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo('fpdf');
    $zip->close();
    echo "FPDF has been downloaded and extracted successfully!";
} else {
    echo "Failed to extract FPDF";
}

// Clean up
unlink($zipFile);
?> 