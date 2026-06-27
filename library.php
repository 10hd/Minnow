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
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Minnow | Library</title>
</head>
<body class="bg-slate-950 cabin min-h-screen text-white bg-linear-to-b from-black via-slate-950 to-slate-950 bg-fixed flex flex-col">

    <header>
        <nav class="p-5 flex items-center justify-between">
            <div class="flex items-baseline gap-6">
                <a href="/" class="text-3xl text-gray-400 hover:text-white transition-colors">Minnow</a>
                <a href="/library" class="text-xl transition-colors">Library</a>
                <a href="/about" class="text-xl text-gray-400 hover:text-white transition-colors">About</a>
            </div>

            <div class="flex gap-4">
                <a href="/library?sort=alpha" class="flex items-center px-3 py-1 rounded-lg <?php echo $sort_mode === 'alpha' ? 'bg-slate-900 text-white' : 'text-gray-400 hover:text-white'; ?>">A-Z</a>
                <a href="/library?sort=latest" class="flex items-center px-3 py-1 rounded-lg <?php echo $sort_mode === 'latest' ? 'bg-slate-900 text-white' : 'text-gray-400 hover:text-white'; ?>">Latest</a>
                <a href="/library?sort=size" class="flex items-center px-3 py-1 rounded-lg <?php echo $sort_mode === 'size' ? 'bg-slate-900 text-white' : 'text-gray-400 hover:text-white'; ?>">Size</a>
                <div class="relative w-64">
                    <input id="search-bar" class="w-full bg-slate-950 border-2 border-slate-900 rounded-lg p-2 focus:outline-none focus:border-slate-800 text-white" placeholder="Search..." autocomplete="off">
                    <div id="search-results" class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-800 rounded-lg shadow-2xl hidden overflow-hidden z-50"></div>
                </div>
            </div>
        </nav>
    </header>
   
    <main class="flex-1 px-15 py-10">
        <?php if (empty($media)): ?>
            <div class="text-center">
                <p class="text-2xl text-gray-400">No media found. Check your directory path.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 w-full">
                <?php foreach ($media as $media): 
                    if (!empty($media['cover_url'])) {
                        $poster_url = $media['cover_url'];
                    } else {
                        $poster_url = "https://placehold.co/400x600/0f172a/94a3b8?text=" . urlencode($media['title']);
                    }
                ?>
                    <a href="/player?<?php echo htmlspecialchars($media['file_name']); ?>" class="group flex flex-col gap-2 cursor-pointer">
                        <div class="relative overflow-hidden rounded-lg border-2 border-slate-900 group-hover:scale-105 duration-400 transition-transform aspect-2/3">
                            <img src="<?php echo htmlspecialchars($poster_url); ?>" 
                                 alt="<?php echo htmlspecialchars($media['title']); ?>" 
                                 class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-lg font-medium tracking-wide truncate mt-1 text-slate-300 group-hover:text-white transition-colors">
                            <?php echo htmlspecialchars($media['title']); ?>
                        </h4>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="flex bg-black w-full justify-center p-5">
        <p>Copyright &copy; <span id="year"></span> Minnow by <a href="https://github.com/10hd" target="_blank" rel="noopener noreferrer" class="hover:text-blue-500 underline transition-colors">10hd</a>. All rights reserved.</p>
    
        <script>
            document.getElementById('year').textContent = new Date().getFullYear();
        </script>
    </footer>

<script src="search.js"></script>

</body>
</html>
