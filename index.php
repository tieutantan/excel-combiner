<?php

set_time_limit(0);

$reload = false;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Performance\Performance;

Performance::point();

$cache = new Cache();

if (empty($cache->get('products')) || $reload) {
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load('export-product-EU.xlsx');
    $sheetData = $spreadsheet->getActiveSheet()->toArray();
    $cache->set('products', $sheetData);
}

if (empty($cache->get('orders')) || $reload) {
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load('export-order-EU.xlsx');
    $sheetData = $spreadsheet->getActiveSheet()->toArray();
    $cache->set('orders', $sheetData);
}

if (empty($cache->get('returns')) || $reload) {
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load('return_products.xlsx');
    $sheetData = $spreadsheet->getActiveSheet()->toArray();
    $cache->set('returns', $sheetData);
}

/*
 * Create products formatted
 */
$productFormat = [];
$products = $cache->get('products');
//dd($products);
array_shift($products); // remove the row's title
foreach ($products as $key => $line) {

    // If that product has variant SKU
    if (!empty($line[2])) {
        $productFormat[$line[2]] = [
            'variant_sku' => $line[2] ?? 'n/a',
            'product_id' => $line[0] ?? 'n/a',
            'url' => $line[18] ?? 'n/a',
            'size' => $line[27]  ?? 'n/a',
            'barcode' => trim($line[4], "'")  ?? 'n/a'
        ];
    }
}

/*
 * Create Orders formatted
 */
$orderFormat = [];
$orders = $cache->get('orders');
//dd($orders);
array_shift($orders); // remove the row's title
foreach ($orders as $key => $line) {

    // If that product has variant SKU
    if (!empty($line[1])) {
        $orderFormat[$line[1]] = [
            'order_number' => $line[1] ?? 'n/a',
            'order_id' => $line[0] ?? 'n/a'
        ];
    }
}

/*
 * Create return products + info
 */
$returnInfo = [];
$returns = $cache->get('returns');
//dd($returns);
array_shift($returns); // remove the row's title
$nonSKU = 0;
foreach ($returns as $key => $line) {
    if (!empty($line[1])) {

        $productInfo = $productFormat[$line[1]];
        $orderInfo = $orderFormat[$line[0]];

        if (!empty($productInfo)) {
            array_unshift($line,
                $orderInfo['order_id'],
                $orderInfo['order_number'],
                $productInfo['variant_sku'],
                $productInfo['url'],
                $productInfo['product_id'],
                $productInfo['size'],
                $productInfo['barcode']);
            $returnInfo[] = $line;
        }
    } else {
        $nonSKU++;
    }
}

// Open a file in write mode ('w')
$fp = fopen('returns_info_27_jun_2021.csv', 'w');

// Loop through file pointer and a line
foreach ($returnInfo as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

Performance::results();