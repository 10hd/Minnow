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

    <header class="relative z-10">
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
        <h1 class="relative z-10 text-3xl mb-6 font-bold tracking-wide text-slate-200 w-full text-left">
            <?php echo htmlspecialchars($clean_title); ?>
        </h1>

        <div class="relative w-full max-w-6xl mx-auto" style="isolation:isolate;">
            <canvas id="ambient-glow" class="absolute inset-0 w-full h-full" style="transform: scale(1.06); filter: blur(50px) saturate(1.3) brightness(0.6); z-index: 0; opacity: 0; transition: opacity .6s;"></canvas>
            
            <div class="relative w-full bg-black rounded-xl overflow-hidden border-2 border-slate-900 aspect-video group" style="z-index: 1;">
                <video id="video-player" src="<?php echo htmlspecialchars($video_url); ?>" class="w-full h-full">Your browser does not support the video tag.</video>

                <div id="controls" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent px-4 pt-10 pb-3 opacity-0 transition-opacity duration-300 z-10">
                    <div id="seek-container" class="w-full py-3 cursor-pointer">
                        <input id="seek-bar" type="range" min="0" max="100" value="0" step="0.1" class="w-full h-1 accent-white pointer-events-none appearance-none rounded-lg outline-none block" style="background: linear-gradient(to right, #ffffff 0%, rgba(255,255,255,0.2) 0%);">
                    </div>

                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center gap-4">
                            <button id="play-pause-btn" class="relative hover:text-slate-300 transition-colors after:content-[''] after:absolute after:-inset-4 after:cursor-pointer">
                                <svg id="play-icon" class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                <svg id="pause-icon" class="w-7 h-7 hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M6 5h4v14H6zm8 0h4v14h-4z"/></svg>
                            </button>

                            <div class="flex items-center gap-2">
                                <button id="mute-btn" class="relative hover:text-slate-300 transition-colors after:content-[''] after:absolute after:-inset-4 after:cursor-pointer">
                                    <svg id="vol-icon" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M3 10v4h4l5 5V5L7 10H3zm13.5 2A4.5 4.5 0 0 0 14 7.97v8.05A4.5 4.5 0 0 0 16.5 12z"/></svg>
                                </button>
                    
                                <div id="volume-container" class="w-20 py-3 cursor-pointer">
                                    <input id="volume-bar" type="range" min="0" max="1" value="0.8" step="0.01" class="w-full h-1 accent-white pointer-events-none appearance-none rounded-lg outline-none block" style="background: linear-gradient(to right, #ffffff 80%, rgba(255,255,255,0.2) 80%);">
                                </div>
                            </div>

                            <span id="time-display" class="text-sm text-slate-300 tabular-nums">00:00 / 00:00</span>
                        </div>

                        <button id="fullscreen-btn" class="relative hover:text-slate-300 transition-colors after:content-[''] after:absolute after:-inset-4 after:cursor-pointer">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="relative z-10 flex bg-black w-full justify-center p-5">
        <p>Copyright &copy; <span id="year"></span> Minnow by <a href="https://github.com/10hd" target="_blank" rel="noopener noreferrer" class="hover:text-blue-500 underline transition-colors">10hd</a>. All rights reserved.</p>
    
        <script>
            document.getElementById('year').textContent = new Date().getFullYear();
        </script>
    </footer>

<script src="search.js"></script>
<script src="glow.js"></script>
<script src="controls.js"></script>

</body>
</html>
