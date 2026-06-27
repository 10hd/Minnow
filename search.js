const searchBar = document.getElementById('search-bar');
const searchResults = document.getElementById('search-results');

searchBar.addEventListener('input', async () => {
    const query = searchBar.value.trim();

    if (query.length < 1) {
        searchResults.innerHTML = '';
        searchResults.classList.add('hidden');
        return;
    }

    try {
        const response = await fetch(`/search?q=${encodeURIComponent(query)}`);
        const media = await response.json();

        if (media.length === 0) {
            searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500">No media found.</div>';
            searchResults.classList.remove('hidden');
            return;
        }

        searchResults.innerHTML = media.map(media => `
            <a href="/player?${encodeURIComponent(media.file)}" class="flex items-center gap-3 p-2 hover:bg-slate-800 transition-colors border-b border-slate-950/40 last:border-0 group">
                <img src="${media.poster}" alt="" class="w-10 h-14 object-cover rounded-md border border-slate-800">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-200 truncate group-hover:text-white">${media.title}</p>
                </div>
            </a>
        `).join('');

        searchResults.classList.remove('hidden');
    } catch (error) {
        console.error('Search failed:', error);
    }
});

document.addEventListener('click', (e) => {
    if (!searchBar.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add('hidden');
    }
});
