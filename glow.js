(function () {
    const video = document.getElementById('video-player');
    const glowCanvas = document.getElementById('ambient-glow');
    const glowCtx = glowCanvas.getContext('2d', { alpha: false });

    const sampleCanvas = document.createElement('canvas');
    const SAMPLE_W = 32, SAMPLE_H = 18;
    sampleCanvas.width = SAMPLE_W;
    sampleCanvas.height = SAMPLE_H;
    const sampleCtx = sampleCanvas.getContext('2d', { willReadFrequently: true });

    let rafId = null;
    let lastDraw = 0;
    const FRAME_INTERVAL = 1000 / 8;

    function resizeGlow() {
        const rect = video.getBoundingClientRect();
        glowCanvas.width = rect.width;
        glowCanvas.height = rect.height;
    }

    function drawGlow(timestamp) {
        rafId = requestAnimationFrame(drawGlow);

        if (timestamp - lastDraw < FRAME_INTERVAL) return;
        lastDraw = timestamp;

        if (video.paused || video.ended || video.readyState < 2) return;

        try {
            sampleCtx.drawImage(video, 0, 0, SAMPLE_W, SAMPLE_H);
            glowCtx.drawImage(sampleCanvas, 0, 0, glowCanvas.width, glowCanvas.height);
        } catch (e) {
        }
    }

    video.addEventListener('loadedmetadata', resizeGlow);
    window.addEventListener('resize', resizeGlow);

    video.addEventListener('play', () => {
        resizeGlow();
        glowCanvas.style.opacity = '1';
        if (!rafId) rafId = requestAnimationFrame(drawGlow);
    });

    video.addEventListener('pause', () => {
        glowCanvas.style.opacity = '0';
    });

    video.addEventListener('ended', () => {
        glowCanvas.style.opacity = '0';
    });
})();
