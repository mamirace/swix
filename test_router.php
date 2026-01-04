<?php
// Test için basit bir router

error_reporting(E_ALL);
ini_set('display_errors', 1);

$request_uri = $_SERVER['REQUEST_URI'];
$uri_path = parse_url($request_uri, PHP_URL_PATH);

echo "Request: $request_uri\n";
echo "Path: $uri_path\n";

// /assets/ test
if (strpos($uri_path, '/assets/') === 0) {
    $varlık_path = str_replace('/assets/', '/varlıklar/', $uri_path);
    $file_path = __DIR__ . $varlık_path;
    
    echo "Varlik path: $varlık_path\n";
    echo "File path: $file_path\n";
    echo "Exists: " . (file_exists($file_path) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($file_path) && is_file($file_path)) {
        echo "Serving file...\n";
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        header("Content-Type: $mime");
        readfile($file_path);
        exit;
    }
}

echo "Done!\n";
