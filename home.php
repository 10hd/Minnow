<?php
$config = require 'config.php';
$media_path = $config['media_path'];

$recent_media = [];

if (is_dir($media_path)) {
    $files = scandir($media_path);
    $media_list = [];

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $full_path = $media_path . DIRECTORY_SEPARATOR . $file;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (is_file($full_path) && in_array($ext, $config['allowed_video'])) {
            $media_list[$file] = filemtime($full_path);
        }
    }

    arsort($media_list);
    $recent_media = array_slice(array_keys($media_list), 0, 5);
}

function findMediaPoster($media_path, $video_filename, $allowed_images) {
    $base_name = pathinfo($video_filename, PATHINFO_FILENAME);

    foreach ($allowed_images as $ext) {
        $img_file = $base_name . '.' . $ext;
        $img_full_path = $media_path . DIRECTORY_SEPARATOR . $img_file;

        if (file_exists($img_full_path)) {
            $cover_path = $media_path . '/' . $img_file;
            return str_replace(__DIR__, '', $cover_path);
        }
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
    
    <link rel="icon" href="/logo/favicon.ico" sizes="any">
    <link rel="icon" href="/logo/icon-192.png" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="/logo/apple-touch-icon.png">
    <link rel="manifest" href="/logo/site.webmanifest">

    <title>Minnow</title>
</head>
<body class="bg-slate-950 cabin h-screen text-white bg-linear-to-b from-black via-slate-950 to-slate-950 flex flex-col">

    <header>
        <nav class="p-5 flex items-center justify-between">
            <div class="flex items-center">
                <a href="/" class="flex items-center"><img src="/logo/icon-192.png" width="60" height="60" class="hidden md:block h-15 w-auto mr-1" alt="Minnow Logo"></a>
                <div class="flex items-baseline gap-4 md:gap-6">
                    <a href="/" class="text-2xl md:text-3xl">Minnow</a>
                    <a href="/library" class="text-md md:text-xl text-gray-400 hover:text-white transition-colors">Library</a>
                    <a href="/about" class="text-md md:text-xl text-gray-400 hover:text-white transition-colors">About</a>
                </div>
            </div>
            <div class="relative w-32 md:w-64">
                <input id="search-bar" class="w-full bg-slate-950 border-2 border-slate-900 rounded-lg p-2 focus:outline-none focus:border-slate-800 text-white" placeholder="Search..." autocomplete="off">
                <div id="search-results" class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-800 rounded-lg shadow-2xl hidden overflow-hidden z-50"></div>
            </div>
        </nav>
    </header>
   
    <main class="flex-1 flex flex-col items-center px-15 py-4">
        <h2 class="text-center text-4xl md:text-5xl font-bold hover:scale-110 duration-800 transition-transform">Welcome to Minnow.</h2>
        <h3 class="text-center text-3xl md:text-4xl tracking-wide mt-6">This is <span class="italic">your</span> media.</h3>
    
        <a href="/library" class="mt-10 md:mt-25 text-xl md:text-2xl px-3 md:px-6 py-1.5 md:py-3 bg-slate-950 hover:bg-slate-900 border-2 border-slate-900 rounded-lg hover:scale-110 duration-400 transition">Open Library</a>

        <h4 class="text-3xl mt-8 md:mt-20">Recently Added</h4>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-8 w-full mt-5 max-w-5xl">
            <?php if (!empty($recent_media)): ?>
                <?php
                $index = 0;
                foreach ($recent_media as $media_file): 
                    $raw_title = pathinfo($media_file, PATHINFO_FILENAME);
                    $clean_title = ucwords(str_replace(['.', '_', '-'], ' ', $raw_title));
            
                    $cover_url = findMediaPoster($media_path, $media_file, $config['allowed_images']);
            
                    if (!empty($cover_url)) {
                        $poster_url = $cover_url;
                    } else {
                        $poster_url = "https://placehold.co/400x600/0f172a/94a3b8?text=" . urlencode($clean_title);
                    }
                    
                    $visibility_class = "block";
                    if ($index === 3) {
                        $visibility_class = "block md:hidden lg:block";
                    } elseif ($index === 4) {
                        $visibility_class = "hidden lg:block";
                    }
                ?>
                <a href="/player?<?php echo urlencode($media_file); ?>" class="<?php echo $visibility_class; ?>">
                    <img src="<?php echo htmlspecialchars($poster_url); ?>"
                        alt="<?php echo htmlspecialchars($clean_title); ?>"
                        class="w-full aspect-2/3 object-cover rounded-lg border-2 border-slate-900 hover:scale-105 duration-400 transition shadow-lg"
                        loading="eager"
                        decoding="async"
                        width="400"
                        height="600">
                </a>
            <?php
                $index++;
            endforeach; ?>
            <?php else: ?>
                <?php for($i = 0; $i < 5; $i++): ?>
                    <div class="relative w-full rounded-lg border-2 border-dashed border-slate-800 bg-slate-900/50 flex items-center justify-center text-gray-500 text-sm font-medium overflow-hidden shadow-lg">
                        <svg class="w-full h-auto pointer-events-none" viewBox="0 0 400 600"></svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            Empty Slot
                        </div>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="flex bg-black w-full justify-center p-5 text-center">
        <p>Copyright &copy; <span id="year"></span> Minnow by <a href="https://github.com/10hd" target="_blank" rel="noopener noreferrer" class="hover:text-blue-500 underline transition-colors">10hd</a>. All rights reserved.</p>
    
        <script>
            document.getElementById('year').textContent = new Date().getFullYear();
        </script>
    </footer>

<script src="search.js"></script>

</body>
</html>
