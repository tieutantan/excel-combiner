<?php

$cacheMinutes = 9999;
$reload = false;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$cache = new Cache();

if (empty($cache->get('products')) || $reload) {
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
    $spreadsheet = $reader->load('products.csv');
    $sheetData = $spreadsheet->getActiveSheet()->toArray();
    $cache->set('products', $sheetData, $cacheMinutes);
}

if (empty($cache->get('returns')) || $reload) {
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
    $spreadsheet = $reader->load('returns.csv');
    $sheetData = $spreadsheet->getActiveSheet()->toArray();
    $cache->set('returns', $sheetData, $cacheMinutes);
}

/*
 * Create products formatted
 */
$productFormat = [];
$product = $cache->get('products');
array_shift($product); // remove the row's title
foreach ($product as $key => $line) {

    // If that product has SKU
    if (!empty($line[13])) {
        $productFormat[$line[13]] = [
            'url' => $line[0] ?? 'n/a',
            'size' => $line[8]  ?? 'n/a',
            'barcode' => trim($line[22], "'")  ?? 'n/a'
        ];
    }
}

/*
 * Create return products + info
 */
$returnInfo = [];
$returns = $cache->get('returns');
array_shift($returns); // remove the row's title
$nonSKU = 0;
foreach ($returns as $key => $line) {
    if (!empty($line[14])) {
        $productInfo = $productFormat[$line[14]];
        if (!empty($productInfo)) {
            $returnUrl = 'https://nu-in.com/products/' . $productInfo['url'];
            array_unshift($line, $returnUrl, $productInfo['size'], $productInfo['barcode']);
            $returnInfo[] = $line;
        }
    } else {
        $nonSKU++;
    }
}


// Open a file in write mode ('w')
$fp = fopen('returns_info.csv', 'w');

// Loop through file pointer and a line
foreach ($returnInfo as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

echo 'done';