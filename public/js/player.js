/* ==========================================================================
   SoundKu — audio player
   Memakai satu elemen <audio> dan HLS.js untuk track yang hanya tersedia
   dalam format HLS (mayoritas track SoundCloud).
   ========================================================================== */

(function () {
    'use strict';

    const SoundKu = (window.SoundKu = window.SoundKu || {});

    const state = {
        queue: [],
        index: -1,
        playing: false,
        loading: false,
        shuffle: false,
        repeat: 'off', // off | all | one
        volume: 0.8,
        muted: false,
        current: null,
        token: 0,
    };

    const elements = {};

    /* --------------------------------------------------------------------- */
    /* Helpers                                                                */
    /* --------------------------------------------------------------------- */

    function el(id) {
        return document.getElementById(id);
    }

    function formatDuration(seconds) {
        return SoundKu.formatDuration ? SoundKu.formatDuration(seconds) : '0:00';
    }

    function toast(message, type) {
        if (SoundKu.toast) {
            SoundKu.toast(message, type || 'info');
        }
    }

    function parseQueue(node) {
        if (!node) {
            return [];
        }

        try {
            const parsed = JSON.parse(node.dataset.queue || '[]');

            return Array.isArray(parsed) ? parsed.filter(function (track) {
                return track && track.id;
            }) : [];
        } catch (error) {
            return [];
        }
    }

    function setRangeFill(range) {
        const min = Number(range.min || 0);
        const max = Number(range.max || 100);
        const value = Number(range.value || 0);
        const percent = max > min ? ((value - min) / (max - min)) * 100 : 0;

        range.style.setProperty('--p', percent.toFixed(2) + '%');
    }

    /* --------------------------------------------------------------------- */
    /* Audio setup                                                           */
    /* --------------------------------------------------------------------- */

    function initAudio() {
        const audio = el('audio');

        audio.volume = state.volume;

        audio.addEventListener('timeupdate', function () {
            if (!audio.duration || !isFinite(audio.duration)) {
                return;
            }

            if (!elements.seeking) {
                elements.seek.value = String((audio.currentTime / audio.duration) * 100);
                setRangeFill(elements.seek);
            }

            elements.current.textContent = formatDuration(audio.currentTime);
        });

        audio.addEventListener('durationchange', function () {
            elements.duration.textContent = formatDuration(audio.duration);
            elements.seek.disabled = !isFinite(audio.duration);
        });

        audio.addEventListener('loadedmetadata', function () {
            if (isFinite(audio.duration) && audio.duration > 0) {
                elements.duration.textContent = formatDuration(audio.duration);
            }
        });

        audio.addEventListener('play', function () {
            state.playing = true;
            renderPlayState();
        });

        audio.addEventListener('pause', function () {
            state.playing = false;
            renderPlayState();
        });

        audio.addEventListener('ended', function () {
            handleEnded();
        });

        audio.addEventListener('error', function () {
            if (state.loading) {
                return;
            }

            toast('Audio gagal diputar. Coba lagu lain.', 'error');
            setLoading(false);
        });

        audio.addEventListener('waiting', function () {
            setLoading(true);
        });

        audio.addEventListener('playing', function () {
            setLoading(false);
        });

        audio.addEventListener('canplay', function () {
            setLoading(false);
        });
    }

    /* --------------------------------------------------------------------- */
    /* HLS                                                                   */
    /* --------------------------------------------------------------------- */

    function detachSource() {
        if (state.hls) {
            try {
                state.hls.destroy();
            } catch (error) {
                // abaikan
            }

            state.hls = null;
        }
    }

    function attachSource(stream) {
        const audio = elements.audio;

        detachSource();
        audio.removeAttribute('src');
        audio.load();

        if (stream.type === 'hls' && window.Hls && window.Hls.isSupported()) {
            const hls = new window.Hls({
                enableWorker: true,
                lowLatencyMode: false,
                backBufferLength: 30,
            });

            state.hls = hls;

            hls.loadSource(stream.url);
            hls.attachMedia(audio);

            hls.on(window.Hls.Events.ERROR, function (event, data) {
                if (!data || !data.fatal) {
                    return;
                }

                if (data.type === window.Hls.ErrorTypes.NETWORK_ERROR) {
                    try {
                        hls.startLoad();
                    } catch (error) {
                        fail();
                    }
                } else if (data.type === window.Hls.ErrorTypes.MEDIA_ERROR) {
                    try {
                        hls.recoverMediaError();
                    } catch (error) {
                        fail();
                    }
                } else {
                    fail();
                }
            });

            return;
        }

        if (stream.type === 'hls' && audio.canPlayType('application/vnd.apple.mpegurl')) {
            audio.src = stream.url;

            return;
        }

        audio.src = stream.url;

        function fail() {
            setLoading(false);
            toast('Streaming gagal dimuat. Coba lagi.', 'error');
        }
    }

    /* --------------------------------------------------------------------- */
    /* Rendering                                                             */
    /* --------------------------------------------------------------------- */

    function renderPlayState() {
        const icon = el('play-icon');

        if (!icon) {
            return;
        }

        icon.innerHTML = state.playing
            ? '<rect x="6.5" y="4.5" width="4" height="15" rx="1" fill="currentColor" stroke="none"/><rect x="13.5" y="4.5" width="4" height="15" rx="1" fill="currentColor" stroke="none"/>'
            : '<path d="M7 4.5v15l13-7.5-13-7.5Z" fill="currentColor" stroke="none"/>';

        elements.toggle.setAttribute('aria-label', state.playing ? 'Jeda' : 'Putar');
    }

    function setLoading(loading) {
        state.loading = loading;
        elements.player.classList.toggle('is-loading', loading);

        if (elements.spinner) {
            elements.spinner.hidden = !loading;
        }
    }

    function renderTrack() {
        const track = state.current;

        if (!track) {
            return;
        }

        elements.art.innerHTML = track.artwork
            ? '<img src="' + SoundKu.escapeHtml(track.artwork) + '" alt="" loading="lazy">'
            : '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>';

        elements.title.hidden = false;
        elements.title.textContent = track.title;
        elements.title.href = '/track/' + encodeURIComponent(track.id);
        elements.title.title = track.title;
        elements.artist.textContent = track.artist || 'Unknown artist';

        elements.toggle.disabled = false;
        elements.seek.disabled = false;

        elements.preview.hidden = !track.is_preview;

        elements.fav.disabled = false;

        if (elements.add) {
            elements.add.disabled = false;
        }
        elements.fav.dataset.fav = JSON.stringify(track);
        elements.fav.dataset.favId = track.id;
        elements.fav.setAttribute('aria-pressed', SoundKu.favorites && SoundKu.favorites.has(track) ? 'true' : 'false');

        if (SoundKu.favorites) {
            SoundKu.favorites.sync(elements.player);
        }

        document.title = track.title + ' — ' + (track.artist || 'SoundKu');

        if (SoundKu.recent) {
            SoundKu.recent.add(track);
        }

        document.dispatchEvent(new CustomEvent('soundku:track-changed'));
        document.dispatchEvent(new CustomEvent('soundku:recent-changed'));

        renderQueue();
    }

    function renderQueue() {
        if (!elements.queueList) {
            return;
        }

        elements.queueBadge.hidden = state.queue.length === 0;
        elements.queueBadge.textContent = String(state.queue.length);

        if (state.queue.length === 0) {
            elements.queueList.innerHTML = '<p class="queue-empty">Antrean masih kosong. Tekan tombol putar di salah satu lagu.</p>';
            elements.queueSummary.textContent = 'Belum ada lagu';

            return;
        }

        elements.queueSummary.textContent = (state.index + 1) + ' / ' + state.queue.length + ' lagu';

        elements.queueList.innerHTML = state.queue.map(function (track, index) {
            return (
                '<div class="queue-item' + (index === state.index ? ' is-current' : '') + '" data-queue-index="' + index + '" role="button" tabindex="0">' +
                    (track.artwork
                        ? '<img class="queue-item-art" src="' + SoundKu.escapeHtml(track.artwork) + '" alt="" loading="lazy">'
                        : '<div class="queue-item-art art-placeholder"></div>') +
                    '<div class="queue-item-main">' +
                        '<span class="queue-item-title">' + SoundKu.escapeHtml(track.title) + '</span>' +
                        '<span class="queue-item-artist">' + SoundKu.escapeHtml(track.artist || '') + '</span>' +
                    '</div>' +
                    '<button class="queue-item-remove" type="button" data-queue-remove="' + index + '" aria-label="Hapus dari antrean">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
                    '</button>' +
                '</div>'
            );
        }).join('');
    }

    /* --------------------------------------------------------------------- */
    /* Playback                                                              */
    /* --------------------------------------------------------------------- */

    function resetAudioUi() {
        elements.current.textContent = '0:00';
        elements.duration.textContent = '0:00';
        elements.seek.value = '0';
        setRangeFill(elements.seek);
    }

    async function playIndex(index, options) {
        const track = state.queue[index];

        if (!track) {
            return;
        }

        const token = ++state.token;

        state.index = index;
        state.current = track;

        renderTrack();
        resetAudioUi();
        setLoading(true);

        try {
            const response = await fetch('/api/stream/' + encodeURIComponent(track.id), {
                headers: { Accept: 'application/json' },
            });

            const data = await response.json().catch(function () {
                return {};
            });

            if (token !== state.token) {
                return;
            }

            if (!response.ok || !data.url) {
                setLoading(false);
                toast(data.error || 'Track ini tidak bisa diputar.', 'error');

                return;
            }

            attachSource(data);

            if (options && options.autoplay === false) {
                setLoading(false);

                return;
            }

            const playPromise = elements.audio.play();

            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function (error) {
                    if (error && error.name === 'NotAllowedError') {
                        setLoading(false);
                        toast('Tekan tombol putar untuk mulai memutar audio.');

                        return;
                    }

                    setLoading(false);
                });
            }
        } catch (error) {
            if (token === state.token) {
                setLoading(false);
                toast('Gagal menghubungi server. Coba lagi.', 'error');
            }
        }
    }

    function togglePlay() {
        if (!state.current) {
            return;
        }

        if (elements.audio.paused) {
            elements.audio.play().catch(function () {
                setLoading(false);
            });
        } else {
            elements.audio.pause();
        }
    }

    function nextIndex() {
        if (state.queue.length === 0) {
            return -1;
        }

        if (state.shuffle && state.queue.length > 1) {
            let candidate = state.index;

            while (candidate === state.index) {
                candidate = Math.floor(Math.random() * state.queue.length);
            }

            return candidate;
        }

        return (state.index + 1) % state.queue.length;
    }

    function next(auto) {
        if (state.queue.length === 0) {
            return;
        }

        if (state.repeat === 'one' && auto) {
            elements.audio.currentTime = 0;
            elements.audio.play().catch(function () {});
            elements.seek.value = '0';
            setRangeFill(elements.seek);

            return;
        }

        const isLast = state.index === state.queue.length - 1;

        if (isLast && state.repeat !== 'all' && auto) {
            state.playing = false;
            renderPlayState();

            return;
        }

        playIndex(nextIndex());
    }

    function previous() {
        if (state.queue.length === 0) {
            return;
        }

        if (elements.audio.currentTime > 3) {
            elements.audio.currentTime = 0;

            return;
        }

        const index = state.index <= 0 ? state.queue.length - 1 : state.index - 1;

        playIndex(index);
    }

    function handleEnded() {
        next(true);
    }

    function playQueue(tracks, startIndex) {
        state.queue = Array.isArray(tracks) ? tracks.filter(function (track) {
            return track && track.id;
        }) : [];

        if (state.queue.length === 0) {
            return;
        }

        playIndex(Math.min(Math.max(startIndex || 0, 0), state.queue.length - 1));
    }

    /* --------------------------------------------------------------------- */
    /* Repeat / shuffle / volume                                             */
    /* --------------------------------------------------------------------- */

    const REPEAT_ICONS = {
        off: '<path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/>',
        all: '<path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/>',
        one: '<path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/><path d="M11 10h1v4"/>',
    };

    function cycleRepeat() {
        const order = ['off', 'all', 'one'];
        state.repeat = order[(order.indexOf(state.repeat) + 1) % order.length];

        const button = el('repeat-btn');

        button.classList.toggle('is-active', state.repeat !== 'off');
        button.setAttribute('aria-label', state.repeat === 'off' ? 'Ulangi: nonaktif' : state.repeat === 'all' ? 'Ulangi: semua lagu' : 'Ulangi: satu lagu');
        button.title = button.getAttribute('aria-label');

        el('repeat-icon').innerHTML = REPEAT_ICONS[state.repeat];

        toast(state.repeat === 'off' ? 'Ulangi dimatikan.' : state.repeat === 'all' ? 'Antrean akan diulang.' : 'Lagu ini akan diulang.');
    }

    function toggleShuffle() {
        state.shuffle = !state.shuffle;

        const button = document.querySelector('[data-action="shuffle"]');

        button.classList.toggle('is-active', state.shuffle);
        button.setAttribute('aria-label', state.shuffle ? 'Acak: aktif' : 'Acak: nonaktif');

        toast(state.shuffle ? 'Mode acak aktif.' : 'Mode acak nonaktif.');
    }

    function applyVolume(value) {
        state.volume = Math.min(Math.max(value, 0), 1);
        elements.audio.volume = state.volume;
        elements.audio.muted = state.volume === 0 ? true : state.muted;

        elements.volume.value = String(state.volume);
        setRangeFill(elements.volume);

        el('volume-icon').innerHTML = (state.muted || state.volume === 0)
            ? '<path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="m22 9-6 6"/><path d="m16 9 6 6"/>'
            : '<path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>';
    }

    function toggleMute() {
        state.muted = !state.muted;
        elements.audio.muted = state.muted;

        applyVolume(state.volume);
    }

    /* --------------------------------------------------------------------- */
    /* Queue drawer                                                          */
    /* --------------------------------------------------------------------- */

    function openQueue() {
        elements.drawer.classList.add('is-open');
        elements.drawer.setAttribute('aria-hidden', 'false');
        elements.scrim.hidden = false;

        window.requestAnimationFrame(function () {
            elements.scrim.classList.add('is-open');
        });
    }

    function closeQueue() {
        elements.drawer.classList.remove('is-open');
        elements.drawer.setAttribute('aria-hidden', 'true');
        elements.scrim.classList.remove('is-open');

        window.setTimeout(function () {
            if (!elements.drawer.classList.contains('is-open')) {
                elements.scrim.hidden = true;
            }
        }, 260);
    }

    function toggleQueue() {
        if (elements.drawer.classList.contains('is-open')) {
            closeQueue();
        } else {
            openQueue();
        }
    }

    /* --------------------------------------------------------------------- */
    /* Wiring                                                                */
    /* --------------------------------------------------------------------- */

    function initElements() {
        elements.player = el('player');
        elements.audio = el('audio');
        elements.art = el('player-art');
        elements.title = el('player-title');
        elements.artist = el('player-artist');
        elements.toggle = el('play-toggle');
        elements.spinner = el('player-spinner');
        elements.seek = el('seek');
        elements.current = el('time-current');
        elements.duration = el('time-duration');
        elements.volume = el('volume');
        elements.fav = el('player-fav');
        elements.add = el('player-add');
        elements.preview = el('preview-pill');
        elements.drawer = el('queue-drawer');
        elements.scrim = el('drawer-scrim');
        elements.queueList = el('queue-list');
        elements.queueBadge = el('queue-badge');
        elements.queueSummary = el('queue-summary');
    }

    function initControls() {
        if (elements.add) {
            elements.add.addEventListener('click', function () {
                if (state.current && SoundKu.openPlaylistModal) {
                    SoundKu.openPlaylistModal(state.current);
                }
            });
        }

        elements.player.addEventListener('click', function (event) {
            const action = event.target.closest('[data-action]');

            if (action) {
                const name = action.dataset.action;

                if (name === 'toggle') {
                    togglePlay();
                } else if (name === 'next') {
                    next(false);
                } else if (name === 'prev') {
                    previous();
                } else if (name === 'shuffle') {
                    toggleShuffle();
                } else if (name === 'repeat') {
                    cycleRepeat();
                } else if (name === 'mute') {
                    toggleMute();
                }

                return;
            }

            if (event.target.closest('#queue-toggle')) {
                toggleQueue();
            }
        });

        elements.seek.addEventListener('input', function () {
            elements.seeking = true;
            setRangeFill(elements.seek);

            if (isFinite(elements.audio.duration) && elements.audio.duration > 0) {
                const time = (Number(elements.seek.value) / 100) * elements.audio.duration;
                elements.current.textContent = formatDuration(time);
            }
        });

        elements.seek.addEventListener('change', function () {
            elements.seeking = false;

            if (isFinite(elements.audio.duration) && elements.audio.duration > 0) {
                elements.audio.currentTime = (Number(elements.seek.value) / 100) * elements.audio.duration;
            }
        });

        elements.volume.addEventListener('input', function () {
            state.muted = false;
            applyVolume(Number(elements.volume.value));
        });

        el('queue-close').addEventListener('click', closeQueue);
        elements.scrim.addEventListener('click', closeQueue);

        el('queue-clear').addEventListener('click', function () {
            elements.audio.pause();
            elements.audio.removeAttribute('src');
            elements.audio.load();

            detachSource();

            state.queue = [];
            state.index = -1;
            state.current = null;
            state.playing = false;

            resetAudioUi();
            renderQueue();
            renderPlayState();

            elements.toggle.disabled = true;
            elements.fav.disabled = true;

            if (elements.add) {
                elements.add.disabled = true;
            }
            elements.preview.hidden = true;
            elements.title.hidden = true;
            elements.artist.textContent = 'Pilih lagu untuk mulai';
            elements.art.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>';
        });

        elements.queueList.addEventListener('click', function (event) {
            const remove = event.target.closest('[data-queue-remove]');

            if (remove) {
                event.stopPropagation();

                const index = Number(remove.dataset.queueRemove);
                const wasCurrent = index === state.index;

                state.queue.splice(index, 1);

                if (wasCurrent) {
                    if (state.queue.length === 0) {
                        el('queue-clear').click();

                        return;
                    }

                    state.index = Math.min(index, state.queue.length - 1);
                    playIndex(state.index);
                } else if (index < state.index) {
                    state.index--;
                }

                renderQueue();

                return;
            }

            const item = event.target.closest('[data-queue-index]');

            if (item) {
                playIndex(Number(item.dataset.queueIndex));
            }
        });

        elements.queueList.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            const item = event.target.closest('[data-queue-index]');

            if (item && !event.target.closest('button')) {
                event.preventDefault();
                playIndex(Number(item.dataset.queueIndex));
            }
        });

        document.addEventListener('keydown', function (event) {
            const target = event.target;
            const typing = target && (
                target.tagName === 'INPUT' ||
                target.tagName === 'TEXTAREA' ||
                target.tagName === 'SELECT' ||
                target.isContentEditable
            );

            if (typing) {
                return;
            }

            if (event.code === 'Space') {
                event.preventDefault();
                togglePlay();
            } else if (event.key === 'ArrowRight' && event.shiftKey) {
                next(false);
            } else if (event.key === 'ArrowLeft' && event.shiftKey) {
                previous();
            }
        });

        window.addEventListener('storage', function (event) {
            if (event.key === 'soundku:favorites') {
                SoundKu.favorites && SoundKu.favorites.sync();
            }
        });
    }

    function initPageQueueButtons() {
        document.addEventListener('click', function (event) {
            const play = event.target.closest('[data-play-index]');

            if (play && !play.closest('.queue-drawer')) {
                event.preventDefault();

                const section = play.closest('[data-queue]');
                const queue = parseQueue(section);

                if (queue.length === 0) {
                    return;
                }

                playQueue(queue, Number(play.dataset.playIndex) || 0);

                return;
            }

            const playAll = event.target.closest('[data-play-all]');

            if (playAll) {
                event.preventDefault();

                const section = playAll.closest('[data-queue]');
                const queue = parseQueue(section);

                if (queue.length === 0) {
                    return;
                }

                playQueue(queue, 0);
            }
        });
    }

    function initMediaSession() {
        if (!('mediaSession' in navigator) || !state.current) {
            return;
        }

        navigator.mediaSession.metadata = new window.MediaMetadata({
            title: state.current.title,
            artist: state.current.artist,
            artwork: state.current.artwork ? [{ src: state.current.artwork, sizes: '500x500', type: 'image/jpeg' }] : [],
        });

        navigator.mediaSession.setActionHandler('play', function () {
            elements.audio.play().catch(function () {});
        });

        navigator.mediaSession.setActionHandler('pause', function () {
            elements.audio.pause();
        });

        navigator.mediaSession.setActionHandler('previoustrack', previous);
        navigator.mediaSession.setActionHandler('nexttrack', function () {
            next(false);
        });
    }

    function init() {
        initElements();

        if (!elements.player || !elements.audio) {
            return;
        }

        initAudio();
        initControls();
        initPageQueueButtons();

        elements.volume.value = String(state.volume);
        setRangeFill(elements.volume);
        setRangeFill(elements.seek);
        applyVolume(state.volume);
        renderPlayState();
        renderQueue();

        document.addEventListener('soundku:track-changed', function () {
            initMediaSession();
        });

        SoundKu.player = {
            playQueue: playQueue,
            playIndex: playIndex,
            toggle: togglePlay,
            next: next,
            previous: previous,
            get current() {
                return state.current;
            },
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
