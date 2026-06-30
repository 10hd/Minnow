const video = document.getElementById('video-player');
const playPauseBtn = document.getElementById('play-pause-btn');
const playIcon = document.getElementById('play-icon');
const pauseIcon = document.getElementById('pause-icon');
const seekBar = document.getElementById('seek-bar');
const volumeBar = document.getElementById('volume-bar');
const muteBtn = document.getElementById('mute-btn');
const timeDisplay = document.getElementById('time-display');
const fullscreenBtn = document.getElementById('fullscreen-btn');
const controls = document.getElementById('controls');
const container = video.closest('.group');

const seekContainer = document.getElementById('seek-container');
const volumeContainer = document.getElementById('volume-container');

let hideTimeout;

function formatTime(t) {
    if (!isFinite(t) || t < 0) return '00:00';
    
    const h = Math.floor(t / 3600);
    const m = Math.floor((t % 3600) / 60);
    const s = Math.floor(t % 60);

    const mm = m.toString().padStart(2, '0');
    const ss = s.toString().padStart(2, '0');

    if (h > 0) {
        const hh = h.toString().padStart(2, '0');
        return `${hh}:${mm}:${ss}`;
    }
    
    return `${mm}:${ss}`;
}

function togglePlay() {
    video.paused ? video.play() : video.pause();
}

function resetControlsTimeout() {
    container.classList.remove('no-cursor');
    controls.classList.remove('opacity-0');
    controls.classList.add('opacity-100');
    
    clearTimeout(hideTimeout);
    
    if (!video.paused && !controls.matches(':hover')) {
        hideTimeout = setTimeout(() => {
            controls.classList.remove('opacity-100');
            controls.classList.add('opacity-0');
            container.classList.add('no-cursor');
        }, 3000);
    }
}

container.addEventListener('mousemove', resetControlsTimeout);
video.addEventListener('click', togglePlay);
playPauseBtn.addEventListener('click', togglePlay);

controls.addEventListener('mouseenter', () => clearTimeout(hideTimeout));
controls.addEventListener('mouseleave', resetControlsTimeout);

video.addEventListener('play', () => {
    playIcon.classList.add('hidden');
    pauseIcon.classList.remove('hidden');
    resetControlsTimeout();
});

video.addEventListener('pause', () => {
    playIcon.classList.remove('hidden');
    pauseIcon.classList.add('hidden');
    resetControlsTimeout();
});

function updateProgress() {
    const percentage = (video.currentTime / video.duration) * 100 || 0;
    seekBar.value = percentage;
    seekBar.style.background = `linear-gradient(to right, #ffffff ${percentage}%, rgba(255,255,255,0.2) ${percentage}%)`;
    timeDisplay.textContent = `${formatTime(video.currentTime)} / ${formatTime(video.duration)}`;
}

video.addEventListener('timeupdate', () => {
    if (!isDraggingSeek) {
        updateProgress();
    }
});
video.addEventListener('loadedmetadata', updateProgress);

let isDraggingSeek = false;

function handleSeekEvent(e) {
    const rect = seekContainer.getBoundingClientRect();
    const thumbRadius = 8; 
    const usableWidth = rect.width - (thumbRadius * 2);
    
    const clickX = e.clientX - rect.left - thumbRadius;
    const percentage = Math.max(0, Math.min(1, clickX / usableWidth));
    
    video.currentTime = percentage * video.duration;
    
    const fillPercent = percentage * 100;
    seekBar.value = fillPercent;
    seekBar.style.background = `linear-gradient(to right, #ffffff ${fillPercent}%, rgba(255,255,255,0.2) ${fillPercent}%)`;
    timeDisplay.textContent = `${formatTime(video.currentTime)} / ${formatTime(video.duration)}`;
}

seekContainer.addEventListener('mousedown', (e) => {
    e.preventDefault(); 
    isDraggingSeek = true;
    handleSeekEvent(e); 
});

window.addEventListener('mousemove', (e) => {
    if (isDraggingSeek) {
        handleSeekEvent(e);
    }
});

window.addEventListener('mouseup', () => {
    isDraggingSeek = false;
});

function updateVolumeStyle() {
    const volPercent = video.muted ? 0 : (video.volume * 100);
    volumeBar.value = video.muted ? 0 : video.volume;
    volumeBar.style.background = `linear-gradient(to right, #ffffff ${volPercent}%, rgba(255,255,255,0.2) ${volPercent}%)`;
}

const storageKey = 'minnow_player_volume';
const savedVolume = localStorage.getItem(storageKey);
video.volume = savedVolume !== null ? parseFloat(savedVolume) : 0.8;
updateVolumeStyle();

let isDraggingVolume = false;

function handleVolumeEvent(e) {
    const rect = volumeContainer.getBoundingClientRect();
    const thumbRadius = 8; 
    const usableWidth = rect.width - (thumbRadius * 2);
    
    const clickX = e.clientX - rect.left - thumbRadius;
    const volumeLevel = Math.max(0, Math.min(1, clickX / usableWidth));
    
    video.volume = volumeLevel;
    video.muted = false;
    updateVolumeStyle();
}

volumeContainer.addEventListener('mousedown', (e) => {
    e.preventDefault(); 
    isDraggingVolume = true;
    handleVolumeEvent(e);
});

window.addEventListener('mousemove', (e) => {
    if (isDraggingVolume) {
        handleVolumeEvent(e);
    }
});

window.addEventListener('mouseup', () => {
    isDraggingVolume = false;
});

video.addEventListener('volumechange', () => {
    localStorage.setItem(storageKey, video.volume);
    updateVolumeStyle();
});

muteBtn.addEventListener('click', () => {
    video.muted = !video.muted;
    updateVolumeStyle();
});

fullscreenBtn.addEventListener('click', () => {
    if (!document.fullscreenElement) {
        container.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
});
