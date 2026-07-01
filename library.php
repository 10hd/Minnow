<?php
$config = require 'config.php';
$media_path = $config['media_path'];
$allowed_ext = $config['allowed_video'];
$image_ext = $config['allowed_images'];

$media = [];

if (is_dir($media_path)) {
    $files = scandir($media_path);

    foreach ($files as $file) {
        $path_info = pathinfo($file);
        $extension = strtolower($path_info['extension'] ?? '');

        if (in_array($extension, $allowed_ext)) {
            $full_path = $media_path . DIRECTORY_SEPARATOR . $file;
            $raw_title = $path_info['filename'];
            $clean_title = ucwords(str_replace(['.', '_', '-'], ' ', $raw_title));
            
            $cover_url = null;
            foreach ($image_ext as $img_ext) {
                $img_file = $raw_title . '.' . $img_ext;
                $img_full_path = $media_path . DIRECTORY_SEPARATOR . $img_file;

                if (file_exists($img_full_path)) {
                    $cover_path = $media_path . '/' . $img_file;
                    $cover_url = str_replace(__DIR__, '', $cover_path);
                    break;
                }
            }

            $media[] = [
                'title' => $clean_title,
                'file_name' => $file,
                'path' => $full_path,
                'cover_url' => $cover_url,
                'date_added' => filemtime($full_path),
                'file_size' => filesize($full_path)
            ];
        }
    }
}

$sort_mode = $_GET['sort'] ?? 'alpha';

if ($sort_mode === 'alpha') {
    usort($media, function($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    });
} elseif ($sort_mode === 'latest') {
    usort($media, function($a, $b) {
        return $b['date_added'] <=> $a['date_added'];
    });
} elseif ($sort_mode === 'size') {
    usort($media, function($a, $b) {
        return $b['file_size'] <=> $a['file_size'];
    });
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

    <title>Minnow | Library</title>
</head>
<body class="bg-slate-950 cabin min-h-screen text-white bg-linear-to-b from-black via-slate-950 to-slate-950 flex flex-col">

    <header>
        <nav class="p-5 flex items-center justify-between">
            <div class="flex items-center">
                <a href="/" class="flex items-center"><img src="/logo/icon-192.png" width="60" height="60" class="hidden md:block h-15 w-auto mr-1" alt="Minnow Logo"></a>
                <div class="flex items-baseline gap-4 md:gap-6">
                    <a href="/" class="text-2xl md:text-3xl text-gray-400 hover:text-white transition-colors">Minnow</a>
                    <a href="/library" class="text-md md:text-xl">Library</a>
                    <a href="/about" class="text-md md:text-xl text-gray-400 hover:text-white transition-colors">About</a>
                </div>
            </div>
            <div class="relative w-32 md:w-64">
                <input id="search-bar" class="w-full bg-slate-950 border-2 border-slate-900 rounded-lg p-2 focus:outline-none focus:border-slate-800 text-white" placeholder="Search..." autocomplete="off">
                <div id="search-results" class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-800 rounded-lg shadow-2xl hidden overflow-hidden z-50"></div>
            </div>
        </nav>
    </header>

    <main class="flex-1 px-8 md-px-15 py-5 md:py-10">
        <?php if (empty($media)): ?>
            <div class="text-center">
                <p class="text-2xl text-gray-400">No media found. Check your directory path.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 w-full">
                <?php foreach ($media as $index => $item): 
                    if (!empty($item['cover_url'])) {
                        $poster_url = $item['cover_url'];
                    } else {
                        $poster_url = "https://placehold.co/400x600/0f172a/94a3b8?text=" . urlencode($item['title']);
                    }
                ?>
                    <a href="/player?<?php echo htmlspecialchars($item['file_name']); ?>" class="group flex flex-col gap-2 cursor-pointer">
                        <div class="relative overflow-hidden rounded-lg border-2 border-slate-900 group-hover:scale-105 duration-400 transition-transform aspect-2/3">
                            <img src="<?php echo htmlspecialchars($poster_url); ?>" 
                                alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                class="w-full h-full object-cover"
                                loading="<?php echo $index < 6 ? 'eager' : 'lazy'; ?>"
                                decoding="async"
                                width="400"
                                height="600">
                        </div>
                        <h4 class="text-lg font-medium tracking-wide truncate mt-1 text-slate-300 group-hover:text-white transition-colors">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </h4>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
