<?php
$config = require 'config.php';
$media_path = $config['media_path'];

if (isset($_GET['stream']) && isset($_GET['file'])) {
    $file_name = basename($_GET['file']); 
    $full_path = $media_path . DIRECTORY_SEPARATOR . $file_name;

    if (!file_exists($full_path)) {
        header("HTTP/1.1 404 Not Found");
        die('File not found.');
    }

    while (ob_get_level()) {
        ob_end_clean();
    }

    $size = filesize($full_path);
    $length = $size;
    $start = 0;
    $end = $size - 1;

    $fp = fopen($full_path, "rb");

    if (isset($_SERVER['HTTP_RANGE'])) {
        $c_start = $start;
        $c_end = $end;

        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        
        if ($range == '-') {
            $c_start = $size - substr($range, 1);
        } else {
            $range = explode('-', $range);
            $c_start = $range[0];
            $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size - 1;
        }
        $c_end = ($c_end > $end) ? $end : $c_end;
        
        if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        $start = $c_start;
        $end = $c_end;
        $length = $end - $start + 1;
        fseek($fp, $start);
        header('HTTP/1.1 206 Partial Content');
    }

    header("Content-Range: bytes $start-$end/$size");
    header("Accept-Ranges: bytes");
    header("Content-Length: " . $length);
    $stream_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $mime_type = ($stream_ext === 'webm') ? 'video/webm' : 'video/mp4';
    header("Content-Type: " . $mime_type);

    $buffer = 8192;
    while (!feof($fp) && ($p = ftell($fp)) <= $end) {
        if ($p + $buffer > $end) {
            $buffer = $end - $p + 1;
        }
        echo fread($fp, $buffer);
        flush();
    }
    fclose($fp);
    exit;
}

$raw_query = $_SERVER['QUERY_STRING'] ?? '';
$file_name = basename($raw_query);

if (!empty($raw_query) && $raw_query !== $file_name && urlencode($raw_query) !== $file_name) {
    header("Location: /player?" . urlencode($file_name));
    exit();
}

$full_media_path = $media_path . DIRECTORY_SEPARATOR . $file_name;
$path_info = pathinfo($file_name);
$extension = strtolower($path_info['extension'] ?? '');

if (empty($file_name) || !in_array($extension, $config['allowed_video']) || !file_exists($full_media_path)) {
    header("Location: /library");
    exit();
}

$clean_title = ucwords(str_replace(['.', '_', '-'], ' ', $path_info['filename']));
$video_url = "/player?stream=1&file=" . urlencode($file_name);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Minnow | <?php echo htmlspecialchars($clean_title); ?></title>
</head>
<body class="bg-slate-950 cabin h-screen text-white bg-linear-to-b from-black via-slate-950 to-slate-950 flex flex-col">

    <header>
        <nav class="p-5 flex items-center justify-between">
            <div class="flex items-baseline gap-6">
                <a href="/" class="text-3xl text-gray-400 hover:text-white transition-colors">Minnow</a>
                <a href="/library" class="text-xl text-gray-400 hover:text-white transition-colors">Library</a>
                <a href="/about" class="text-xl text-gray-400 hover:text-white transition-colors">About</a>
            </div>
            <div class="relative w-64">
                <input id="search-bar" class="w-full bg-slate-950 border-2 border-slate-900 rounded-lg p-2 focus:outline-none focus:border-slate-800 text-white" placeholder="Search..." autocomplete="off">
                <div id="search-results" class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-800 rounded-lg shadow-2xl hidden overflow-hidden z-50"></div>
            </div>
        </nav>
    </header>
   
    <main class="flex-1 flex flex-col items-center px-6 py-10 max-w-6xl mx-auto w-full">
        <h1 class="text-3xl mb-6 font-bold tracking-wide text-slate-200 w-full text-left">
            <?php echo htmlspecialchars($clean_title); ?>
        </h1>

        <div class="w-full max-w-6xl bg-black rounded-xl overflow-hidden border-2 border-slate-900 aspect-video">
            <video id="video-player" src="<?php echo htmlspecialchars($video_url); ?>" controls class="w-full h-full">Your browser does not support the video tag.</video>
        </div>
    </main>

    <footer class="flex bg-black w-full justify-center p-5">
        <p>Copyright &copy; <span id="year"></span> Minnow by <a href="https://github.com/10hd" target="_blank" rel="noopener noreferrer" class="hover:text-blue-500 underline transition-colors">10hd</a>. All rights reserved.</p>
    
        <script>
            document.getElementById('year').textContent = new Date().getFullYear();
        </script>
    </footer>

    <script>
        const video = document.getElementById('video-player');
        const storageKey = 'minnow_player_volume';

        const savedVolume = localStorage.getItem(storageKey);
        if (savedVolume !== null) {
            video.volume = parseFloat(savedVolume);
        } else {
            video.volume = 0.8; 
        }

        video.addEventListener('volumechange', () => {
            localStorage.setItem(storageKey, video.volume);
        });
    </script>

<script src="search.js"></script>

</body>
</html>
