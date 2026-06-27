<?php
$config = require 'config.php';
$media_path = $config['media_path'];

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim(strtolower($_GET['q'])) : '';
$results = [];

if (!empty($query) && is_dir($media_path)) {
    $files = scandir($media_path);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $config['allowed_video'])) {
            $raw_title = pathinfo($file, PATHINFO_FILENAME);
            $title = str_replace(['.', '_', '-'], ' ', $raw_title);
            
            if (strpos(strtolower($file), $query) !== false || strpos(strtolower($title), $query) !== false) {
                
                $poster = null;
                foreach ($config['allowed_images'] as $img_ext) {
                    $img_file = $raw_title . '.' . $img_ext;
                    $img_full_path = $media_path . DIRECTORY_SEPARATOR . $img_file;        
                    if (file_exists($img_full_path)) {
                        $cover_path = $media_path . '/' . $img_file;
                        $poster = str_replace(__DIR__, '', $cover_path);
                        break;
                    }
                }

                $results[] = [
                    'file' => $file,
                    'title' => ucwords($title),
                    'poster' => $poster ?? "https://placehold.co/400x600/0f172a/94a3b8?text=" . urlencode(ucwords($title))
                ];
            }
        }
    }
}

echo json_encode(array_slice($results, 0, 5));
exit();
