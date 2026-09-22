// mediaPlayer.js
(function () {
    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    function createMediaPlayer(mediaEl, options = {}, isAudio = true) {
        const {
            idSoal = '',
            maxPlay = 3,
            played = 0,
            isPreview = false,
            callback = () => {}
        } = options;

        let remaining = Math.max(0, maxPlay - played);
        let diputar = 0;

        const mediaId = mediaEl.id || (isAudio ? 'audio' : 'video') + '-' + Date.now();
        const suffix = isAudio ? 'Audio' : 'Video';
        const unique = mediaId + '-' + Math.random().toString(36).slice(2, 7);

        const playId = `play-${unique}`;
        const curId = `cur-${unique}`;
        const durId = `dur-${unique}`;
        const barId = `bar-${unique}`;
        const countId = `count-${unique}`;
        const fullScreenId = `fs-${unique}`;

        // Buat wrapper
        const wrapper = document.createElement('div');
        wrapper.className = `media-container ${isAudio ? 'audio-player' : 'video-player'}`;

        // Clone media (untuk menghindari DOM conflict)
        const clonedMedia = mediaEl.cloneNode(true);
        clonedMedia.setAttribute('preload', 'metadata');
        clonedMedia.id = mediaId;
        clonedMedia.removeAttribute('controls');

        const info = isPreview ? `Siswa diijinkan memutar media ini ${maxPlay} kali.` : `diputar: ${played}x, sisa: ${remaining}x`;
        wrapper.innerHTML = `
            ${clonedMedia.outerHTML}
            <div class="controls">
                <button type="button" id="${playId}" class="btn btn-success btn-circle-sm"><i class="fa fa-play" style="margin-left: 2px"></i></button>
                <div class="time-display text-gray">
                    <span id="${curId}">0:00</span> / <span id="${durId}">0:00</span>
                </div>
                <input type="range" id="${barId}" class="play-progress-bar" value="0" min="0" max="100" ${isPreview ? '' : 'disabled'}>
                <button type="button" id="${fullScreenId}" class="fullscreen-btn btn ${isAudio ? 'd-none' : ''}"><i class="text-lg text-gray fa fa-expand"></i></button>
            </div>
            <hr class="my-1">
            <div class="row">
                <span class="play-count-display text-xs ml-2 font-italic" id="${countId}">${info}</span>
            </div>
        `;

        // Ganti elemen media lama dengan wrapper
        const parent = mediaEl.parentNode;
        parent.replaceChild(wrapper, mediaEl);

        // Ambil elemen hasil render baru
        const player = wrapper.querySelector(isAudio ? 'audio' : 'video');
        const playBtn = wrapper.querySelector(`#${playId}`);
        const currentTimeEl = wrapper.querySelector(`#${curId}`);
        const durationEl = wrapper.querySelector(`#${durId}`);
        const progressBar = wrapper.querySelector(`#${barId}`);
        const playCountEl = wrapper.querySelector(`#${countId}`);
        const fullscreenBtn = wrapper.querySelector(`#${fullScreenId}`);

        playBtn.disabled = remaining <= 0;
        playCountEl.style.color = isPreview ? 'grey' : (remaining <= 0 ? 'red' : 'green');

        fullscreenBtn.addEventListener('click', () => {
            if (document.fullscreenElement === wrapper) {
                fullscreenBtn.innerHTML = `<i class="text-lg text-gray fa fa-expand"></i>`;
                document.exitFullscreen();
            } else {
                fullscreenBtn.innerHTML = `<i class="text-lg text-gray fa fa-compress"></i>`;
                wrapper.requestFullscreen().catch(err => {
                    console.warn("Gagal masuk fullscreen:", err);
                });
            }
        });

        player.addEventListener('loadedmetadata', () => {
            durationEl.textContent = formatTime(player.duration);
        });

        player.addEventListener('timeupdate', () => {
            currentTimeEl.textContent = formatTime(player.currentTime);
            progressBar.value = (player.currentTime / player.duration) * 100 || 0;
        });

        progressBar.addEventListener('input', () => {
            if (isPreview) player.currentTime = (progressBar.value / 100) * player.duration;
        });

        playBtn.addEventListener('click', () => {
            if (isPreview) {
                if (player.paused) {
                    player.play();
                    playBtn.innerHTML = '<i class="fa fa-pause"></i>';
                    callback(idSoal, remaining);
                } else {
                    player.pause();
                    playBtn.innerHTML = '<i class="fa fa-play" style="margin-left: 2px"></i>';
                }
            } else {
                if (remaining > 0) {
                    player.play();
                    remaining--;
                    playBtn.disabled = true;
                    diputar = maxPlay - remaining;
                    playCountEl.textContent = `diputar: ${diputar}x, sisa: ${remaining}x`;
                    playCountEl.style.color = remaining <= 0 ? 'red' : 'green';

                    callback(idSoal, diputar);
                }
            }
        });

        player.addEventListener('ended', () => {
            progressBar.value = 0;
            playBtn.innerHTML = '<i class="fa fa-play" style="margin-left: 2px"></i>';
            if (!isPreview) {
                playBtn.disabled = remaining <= 0;
            }
        });
    }

    HTMLAudioElement.prototype.renderAudio = function (options = {}) {
        const {
            idSoal = '',
            maxPlay = 3,
            played = 0,
            isPreview = false,
            callback = () => {}
        } = options;

        createMediaPlayer(this, {
            idSoal,
            maxPlay,
            played,
            isPreview,
            callback
        }, true);
    };

    HTMLVideoElement.prototype.renderVideo = function (options = {}) {
        const {
            idSoal = '',
            maxPlay = 3,
            played = 0,
            isPreview = false,
            callback = () => {}
        } = options;

        createMediaPlayer(this, {
            idSoal,
            maxPlay,
            played,
            isPreview,
            callback
        }, false);
    };
})();
