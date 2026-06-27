<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Minnow | About</title>
</head>
<body class="bg-slate-950 cabin h-screen text-white bg-linear-to-b from-black via-slate-950 to-slate-950 flex flex-col">

    <header>
        <nav class="p-5 flex items-center justify-between">
            <div class="flex items-baseline gap-6">
                <a href="/" class="text-3xl text-gray-400 hover:text-white transition-colors">Minnow</a>
                <a href="/library" class="text-xl text-gray-400 hover:text-white transition-colors">Library</a>
                <a href="/about" class="text-xl">About</a>
            </div>
            <div class="relative w-64">
                <input id="search-bar" class="w-full bg-slate-950 border-2 border-slate-900 rounded-lg p-2 focus:outline-none focus:border-slate-800 text-white" placeholder="Search..." autocomplete="off">
                <div id="search-results" class="absolute left-0 right-0 mt-2 bg-slate-900 border border-slate-800 rounded-lg shadow-2xl hidden overflow-hidden z-50"></div>
            </div>
        </nav>
    </header>
   
    <main class="flex-1 flex flex-col items-center justify-center px-15 py-4 gap-20">
        <div class="w-full max-w-5xl">
            <h2 class="text-4xl">The "Why"</h2>
            <p class="text-2xl mt-5">I wanted a way to stream my local media directly in the browser. I could've just used a media player software but I wanted my own design and I like using websites.</p>
        </div>

        <div class="w-full max-w-5xl">
            <h3 class="text-3xl">How to Add Media</h3>
            <ol>
                <li class="text-2xl mt-5"><span class="font-bold">Configure: </span>Open config.php and point the 'media_path' variable to the directory where your video files are stored. I would advise you make the directory in the same directory as the website files.</li>
                <li class="text-2xl mt-5"><span class="font-bold">Add Your Files: </span>Move your media files into that directory. Optionally, add corresponding images for the media. Minnow will try to automatically scan for supported formats.</li>
            </ol>
            <p class="text-2xl text-gray-400 italic mt-15">OBS! The images will only work if they have the exact same name as the media file. E.g. filename123.mp4 and filename123.jpg</p>
        
        </div>
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
