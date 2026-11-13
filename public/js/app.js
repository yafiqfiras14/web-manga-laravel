/* ==========================================================================
   SoundKu â€” UI helpers, library (likes/history/playlists), search suggestions
   ========================================================================== */

(function () {
    'use strict';

    const SoundKu = (window.SoundKu = window.SoundKu || {});
    const config = window.SoundKuConfig || {};

    const THEME_KEY = 'soundku:theme';

    const likedIds = new Set((config.likedIds || []).map(String));
    let playlists = Array.isArray(config.playlists) ? config.playlists.slice() : [];
    let modalTrack = null;
    let creatingPlaylist = false;

    const PLAY_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4.5v15l13-7.5-13-7.5Z"/></svg>';
    const HEART_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>';
    const MUSIC_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>';
    const PLUS_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>';
    const CHECK_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L20 7"/></svg>';

    /* --------------------------------------------------------------------- */
    /* Helpers                                                                */
    /* --------------------------------------------------------------------- */

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            }[character];
        });
    }

    function formatDuration(seconds) {
        seconds = Math.max(0, Math.floor(Number(seconds) || 0));

        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const rest = seconds % 60;

        if (hours > 0) {
            return hours + ':' + String(minutes).padStart(2, '0') + ':' + String(rest).padStart(2, '0');
        }

        return minutes + ':' + String(rest).padStart(2, '0');
    }

    function trackUrl(track) {
        return '/track/' + encodeURIComponent(track.id);
    }

    function artistUrl(track) {
        return track.artist_id ? '/artist/' + encodeURIComponent(track.artist_id) : null;
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');

        return (config.csrfToken || (meta ? meta.content : '')) || '';
    }

    function endpoint(key) {
        return (config.urls && config.urls[key]) || '';
    }

    function playlistUrl(id) {
        return String(endpoint('playlist')).replace('__ID__', encodeURIComponent(id));
    }

    function request(url, method, body) {
        return fetch(url, {
            method: method || 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: body === undefined ? undefined : JSON.stringify(body),
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (!response.ok) {
                    let message = data && data.message;

                    if (data && data.errors) {
                        const first = Object.keys(data.errors)[0];
                        message = (first && data.errors[first] && data.errors[first][0]) || message;
                    }

                    throw new Error(message || 'Ada yang error. Coba lagi ya.');
                }

                return data || {};
            });
        });
    }

    /* --------------------------------------------------------------------- */
    /* Theme                                                                  */
    /* --------------------------------------------------------------------- */

    function storedTheme() {
        try {
            const value = window.localStorage.getItem(THEME_KEY);

            return value === 'light' || value === 'dark' ? value : null;
        } catch (error) {
            return null;
        }
    }

    function currentTheme() {
        return document.documentElement.dataset.theme === 'light' ? 'light' : 'dark';
    }

    function applyTheme(theme, persist) {
        theme = theme === 'light' ? 'light' : 'dark';

        document.documentElement.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;

        if (persist !== false) {
            try {
                window.localStorage.setItem(THEME_KEY, theme);
            } catch (error) {
                // Tema tetap jalan walau storage diblokir.
            }
        }

        const meta = document.getElementById('theme-color-meta');

        if (meta) {
            meta.setAttribute('content', theme === 'light' ? '#f4f5fa' : '#08090d');
        }

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            const label = theme === 'light' ? 'Ganti ke mode gelap' : 'Ganti ke mode terang';

            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
            button.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
        });
    }

    function toggleTheme() {
        applyTheme(currentTheme() === 'light' ? 'dark' : 'light');
    }

    function initTheme() {
        applyTheme(currentTheme(), false);

        if (!window.matchMedia) {
            return;
        }

        const media = window.matchMedia('(prefers-color-scheme: light)');
        const onChange = function (event) {
            if (!storedTheme()) {
                applyTheme(event.matches ? 'light' : 'dark', false);
            }
        };

        if (media.addEventListener) {
            media.addEventListener('change', onChange);
        } else if (media.addListener) {
            media.addListener(onChange);
        }
    }

    /* --------------------------------------------------------------------- */
    /* Toast                                                                  */
    /* --------------------------------------------------------------------- */

    function toast(message, type) {
        const container = document.getElementById('toasts');

        if (!container) {
            return;
        }

        const element = document.createElement('div');
        element.className = 'toast' + (type ? ' toast-' + type : '');
        element.setAttribute('role', 'status');
        element.textContent = message;
        container.appendChild(element);

        window.setTimeout(function () {
            element.classList.add('is-leaving');
            window.setTimeout(function () {
                element.remove();
            }, 250);
        }, 3200);
    }

    /* --------------------------------------------------------------------- */
    /* Liked songs                                                            */
    /* --------------------------------------------------------------------- */

    function hasFavorite(track) {
        return !!(track && track.id && likedIds.has(String(track.id)));
    }

    function toggleFavorite(track) {
        if (!track || !track.id) {
            return Promise.resolve(false);
        }

        const id = String(track.id);
        const wasLiked = likedIds.has(id);

        if (wasLiked) {
            likedIds.delete(id);
        } else {
            likedIds.add(id);
        }

        syncFavButtons();

        return request(endpoint('like'), 'POST', { track: track })
            .then(function (data) {
                if (data && typeof data.liked === 'boolean') {
                    if (data.liked) {
                        likedIds.add(id);
                    } else {
                        likedIds.delete(id);
                    }
                }

                syncFavButtons();
                toast(data.message || (likedIds.has(id) ? 'Masuk ke Lagu yang disukai.' : 'Dihapus dari Lagu yang disukai.'), likedIds.has(id) ? 'success' : 'info');
                document.dispatchEvent(new CustomEvent('soundku:favorites-changed', {
                    detail: { track: track, liked: likedIds.has(id) },
                }));

                return likedIds.has(id);
            })
            .catch(function (error) {
                if (wasLiked) {
                    likedIds.add(id);
                } else {
                    likedIds.delete(id);
                }

                syncFavButtons();
                toast(error.message, 'error');

                return wasLiked;
            });
    }

    function syncFavButtons(root) {
        (root || document).querySelectorAll('[data-fav-id]').forEach(function (button) {
            let track = null;

            try {
                track = JSON.parse(button.dataset.fav || 'null');
            } catch (error) {
                track = null;
            }

            const active = track ? hasFavorite(track) : false;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
            button.setAttribute('aria-label', active ? 'Hapus dari Lagu yang disukai' : 'Tambahkan ke Lagu yang disukai');
        });
    }

    function removeLikedRow(track) {
        const list = document.getElementById('liked-list');

        if (!list || !track || !track.id) {
            return;
        }

        list.querySelectorAll('[data-track-id]').forEach(function (row) {
            if (row.dataset.trackId === String(track.id)) {
                row.remove();
            }
        });

        if (list.querySelectorAll('[data-track-id]').length === 0) {
            window.location.reload();
        }
    }

    /* --------------------------------------------------------------------- */
    /* Play history                                                           */
    /* --------------------------------------------------------------------- */

    function addRecent(track) {
        if (!track || !track.id || !endpoint('history')) {
            return Promise.resolve();
        }

        return request(endpoint('history'), 'POST', { track: track })
            .then(function () {
                document.dispatchEvent(new CustomEvent('soundku:history-changed'));
            })
            .catch(function () {
                // Riwayat bersifat best-effort; jangan ganggu pemutaran.
            });
    }

    function clearHistory() {
        if (!window.confirm('Hapus semua riwayat putar? Lagu yang disukai dan playlist tetap aman.')) {
            return;
        }

        request(endpoint('clearHistory'), 'DELETE')
            .then(function (data) {
                toast(data.message || 'Riwayat putar dibersihkan.', 'success');

                if (document.getElementById('history-list')) {
                    window.location.reload();
                } else {
                    const section = document.getElementById('recent-section');

                    if (section) {
                        section.remove();
                    }
                }
            })
            .catch(function (error) {
                toast(error.message, 'error');
            });
    }

    /* --------------------------------------------------------------------- */
    /* Playlist modal                                                         */
    /* --------------------------------------------------------------------- */

    function modalElements() {
        return {
            modal: document.getElementById('playlist-modal'),
            list: document.getElementById('playlist-modal-list'),
            form: document.getElementById('playlist-create-form'),
            name: document.getElementById('playlist-create-name'),
            note: document.getElementById('playlist-modal-note'),
            title: document.getElementById('playlist-modal-title'),
            sub: document.getElementById('playlist-modal-sub'),
            track: document.getElementById('playlist-modal-track'),
            art: document.getElementById('playlist-modal-art'),
            trackTitle: document.getElementById('playlist-modal-track-title'),
            trackArtist: document.getElementById('playlist-modal-track-artist'),
        };
    }

    function renderModalPlaylists() {
        const elements = modalElements();

        if (!elements.list) {
            return;
        }

        if (!modalTrack) {
            elements.list.hidden = true;
            elements.list.innerHTML = '';

            return;
        }

        elements.list.hidden = false;

        if (playlists.length === 0) {
            elements.list.innerHTML = '<p class="playlist-modal-empty">Belum ada playlist. Bikin yang pertama di bawah.</p>';

            return;
        }

        elements.list.innerHTML = playlists.map(function (playlist) {
            const cover = playlist.cover_url
                ? '<img src="' + escapeHtml(playlist.cover_url) + '" alt="" loading="lazy">'
                : MUSIC_ICON;

            return (
                '<button class="playlist-modal-item" type="button" data-playlist-add="' + escapeHtml(playlist.id) + '">' +
                    '<span class="playlist-modal-item-art">' + cover + '</span>' +
                    '<span class="playlist-modal-item-main">' +
                        '<strong>' + escapeHtml(playlist.name) + '</strong>' +
                        '<small>' + (Number(playlist.track_count) || 0) + ' lagu</small>' +
                    '</span>' +
                    '<span class="playlist-modal-item-action">' + PLUS_ICON + '</span>' +
                '</button>'
            );
        }).join('');
    }

    function openPlaylistModal(track) {
        const elements = modalElements();

        if (!elements.modal) {
            return;
        }

        modalTrack = track && track.id ? track : null;
        creatingPlaylist = !modalTrack;

        if (modalTrack) {
            elements.track.hidden = false;
            elements.art.innerHTML = modalTrack.artwork
                ? '<img src="' + escapeHtml(modalTrack.artwork) + '" alt="" loading="lazy">'
                : MUSIC_ICON;
            elements.trackTitle.textContent = modalTrack.title;
            elements.trackArtist.textContent = modalTrack.artist || 'Unknown artist';
            elements.title.textContent = 'Simpan ke playlist';
            elements.sub.textContent = 'Pilih playlist tujuan, atau bikin playlist baru.';
        } else {
            elements.track.hidden = true;
            elements.title.textContent = 'Buat playlist baru';
            elements.sub.textContent = 'Kasih nama playlist, langsung jadi.';
        }

        if (elements.note) {
            elements.note.textContent = modalTrack
                ? 'Playlist bisa diganti nama atau dihapus kapan saja dari halaman Perpustakaan.'
                : 'Setelah dibuat, kamu bisa langsung menambahkan lagu dari kartu lagu mana pun.';
        }

        if (elements.name) {
            elements.name.value = '';
        }

        renderModalPlaylists();
        elements.modal.hidden = false;
        document.body.classList.add('modal-open');

        window.setTimeout(function () {
            if (elements.name) {
                elements.name.focus();
            }
        }, 40);
    }

    function closePlaylistModal() {
        const elements = modalElements();

        if (!elements.modal) {
            return;
        }

        elements.modal.hidden = true;
        document.body.classList.remove('modal-open');
        modalTrack = null;
        creatingPlaylist = false;
    }

    function addTrackToPlaylist(playlistId) {
        if (!modalTrack) {
            return;
        }

        const playlist = playlists.find(function (item) {
            return String(item.id) === String(playlistId);
        });

        const button = document.querySelector('[data-playlist-add="' + playlistId + '"]');

        if (button) {
            button.disabled = true;
        }

        request(String(endpoint('addTrack')).replace('__ID__', encodeURIComponent(playlistId)), 'POST', { track: modalTrack })
            .then(function (data) {
                if (playlist && typeof data.track_count === 'number') {
                    playlist.track_count = data.track_count;
                }

                renderModalPlaylists();
                renderSidebarPlaylists();
                toast(data.message || 'Lagu ditambahkan ke playlist.', 'success');

                if (button) {
                    button.classList.add('is-added');
                }
            })
            .catch(function (error) {
                toast(error.message, 'error');

                if (button) {
                    button.disabled = false;
                }
            });
    }

    function createPlaylist(name) {
        const elements = modalElements();
        const submit = elements.form ? elements.form.querySelector('button[type="submit"]') : null;

        if (submit) {
            submit.disabled = true;
        }

        return request(endpoint('createPlaylist'), 'POST', { name: name })
            .then(function (data) {
                if (data.playlist) {
                    playlists.unshift(data.playlist);
                    renderModalPlaylists();
                    renderSidebarPlaylists();
                }

                toast(data.message || 'Playlist dibuat.', 'success');

                if (modalTrack && data.playlist) {
                    addTrackToPlaylist(data.playlist.id);
                } else {
                    closePlaylistModal();
                }

                return data;
            })
            .catch(function (error) {
                toast(error.message, 'error');
            })
            .then(function () {
                if (submit) {
                    submit.disabled = false;
                }
            });
    }

    function renderSidebarPlaylists() {
        const container = document.getElementById('sidebar-playlists');

        if (!container) {
            return;
        }

        if (playlists.length === 0) {
            container.innerHTML = '<p class="sidebar-playlist-empty">Belum ada playlist. Bikin yang pertama!</p>';

            return;
        }

        container.innerHTML = playlists.slice(0, 8).map(function (playlist) {
            const art = playlist.cover_url
                ? '<img src="' + escapeHtml(playlist.cover_url) + '" alt="" loading="lazy">'
                : '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h13"/><path d="M3 12h10"/><path d="M3 18h7"/><circle cx="18" cy="16" r="3"/><path d="M21 16V6l-3 1"/></svg>';

            return (
                '<a class="sidebar-playlist" href="' + escapeHtml(playlistUrl(playlist.id)) + '">' +
                    '<span class="sidebar-playlist-art">' + art + '</span>' +
                    '<span class="sidebar-playlist-main">' +
                        '<strong>' + escapeHtml(playlist.name) + '</strong>' +
                        '<small>' + (Number(playlist.track_count) || 0) + ' lagu</small>' +
                    '</span>' +
                '</a>'
            );
        }).join('');
    }

    /* --------------------------------------------------------------------- */
    /* Client-rendered cards                                                  */
    /* --------------------------------------------------------------------- */

    function artworkMarkup(track, className, iconSize) {
        if (track.artwork) {
            return '<img class="' + className + '" src="' + escapeHtml(track.artwork) + '" alt="" loading="lazy" decoding="async">';
        }

        return '<div class="' + className + ' art-placeholder">' + (iconSize ? MUSIC_ICON : '') + '</div>';
    }

    function renderTrackCard(track, index) {
        const artist = artistUrl(track);

        return (
            '<article class="track-card" data-track-id="' + escapeHtml(track.id) + '">' +
                '<div class="track-art">' +
                    (track.artwork
                        ? '<img src="' + escapeHtml(track.artwork) + '" alt="" loading="lazy" decoding="async">'
                        : '<div class="art-placeholder">' + MUSIC_ICON + '</div>') +
                    '<button class="card-play" type="button" data-play-index="' + index + '" aria-label="Putar ' + escapeHtml(track.title) + '">' + PLAY_ICON + '</button>' +
                    (track.is_preview ? '<span class="preview-badge">30 detik</span>' : '') +
                    '<button class="fav-btn card-fav" type="button" data-fav="' + escapeHtml(JSON.stringify(track)) + '" data-fav-id="' + escapeHtml(track.id) + '" aria-label="Favorit" aria-pressed="false">' + HEART_ICON + '</button>' +
                    '<button class="card-add" type="button" data-add-track="' + escapeHtml(JSON.stringify(track)) + '" aria-label="Simpan ke playlist" title="Simpan ke playlist">' + PLUS_ICON + '</button>' +
                '</div>' +
                '<div class="track-card-body">' +
                    '<a class="track-card-title" href="' + trackUrl(track) + '">' + escapeHtml(track.title) + '</a>' +
                    (artist
                        ? '<a class="track-card-artist" href="' + artist + '">' + escapeHtml(track.artist) + '</a>'
                        : '<span class="track-card-artist">' + escapeHtml(track.artist) + '</span>') +
                '</div>' +
            '</article>'
        );
    }

    function renderTrackRow(track, index) {
        const artist = artistUrl(track);

        return (
            '<div class="track-row" data-track-id="' + escapeHtml(track.id) + '">' +
                '<div class="row-leading">' +
                    '<span class="row-index">' + String(index + 1).padStart(2, '0') + '</span>' +
                    '<div class="row-art-wrap">' +
                        artworkMarkup(track, 'row-art', false) +
                        '<button class="row-play" type="button" data-play-index="' + index + '" aria-label="Putar ' + escapeHtml(track.title) + '">' + PLAY_ICON + '</button>' +
                    '</div>' +
                '</div>' +
                '<div class="row-main">' +
                    '<a class="row-title" href="' + trackUrl(track) + '">' + escapeHtml(track.title) + '</a>' +
                    (artist
                        ? '<a class="row-artist" href="' + artist + '">' + escapeHtml(track.artist) + '</a>'
                        : '<span class="row-artist">' + escapeHtml(track.artist) + '</span>') +
                '</div>' +
                '<span class="row-genre">' + escapeHtml(track.genre || 'â€”') + '</span>' +
                '<span class="row-duration">' + formatDuration(track.duration) + '</span>' +
                '<button class="row-add" type="button" data-add-track="' + escapeHtml(JSON.stringify(track)) + '" aria-label="Simpan ke playlist" title="Simpan ke playlist">' + PLUS_ICON + '</button>' +
                '<button class="fav-btn row-fav" type="button" data-fav="' + escapeHtml(JSON.stringify(track)) + '" data-fav-id="' + escapeHtml(track.id) + '" aria-label="Favorit" aria-pressed="false">' + HEART_ICON + '</button>' +
                '<a class="row-link" href="' + trackUrl(track) + '" aria-label="Detail">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>' +
                '</a>' +
            '</div>'
        );
    }

    /* --------------------------------------------------------------------- */
    /* Search suggestions                                                     */
    /* --------------------------------------------------------------------- */

    function initSearch() {
        const input = document.getElementById('global-search');
        const panel = document.getElementById('search-suggestions');

        if (!input || !panel) {
            return;
        }

        let timer = null;
        let controller = null;
        let activeIndex = -1;

        function close() {
            panel.hidden = true;
            panel.innerHTML = '';
            activeIndex = -1;
        }

        function render(query, tracks) {
            if (!tracks.length) {
                panel.innerHTML = '<p class="suggestion-empty">Tidak ada hasil untuk &ldquo;' + escapeHtml(query) + '&rdquo;.</p>';
                panel.hidden = false;

                return;
            }

            panel.innerHTML =
                tracks.map(function (track) {
                    return (
                        '<a class="suggestion" href="' + trackUrl(track) + '">' +
                            (track.artwork
                                ? '<img class="suggestion-art" src="' + escapeHtml(track.artwork) + '" alt="" loading="lazy">'
                                : '<div class="suggestion-art art-placeholder"></div>') +
                            '<span class="suggestion-main">' +
                                '<span class="suggestion-title">' + escapeHtml(track.title) + '</span>' +
                                '<span class="suggestion-artist">' + escapeHtml(track.artist) + '</span>' +
                            '</span>' +
                        '</a>'
                    );
                }).join('') +
                '<a class="suggestion-all" href="/search?q=' + encodeURIComponent(query) + '">Lihat semua hasil untuk &ldquo;' + escapeHtml(query) + '&rdquo;</a>';

            panel.hidden = false;
            activeIndex = -1;
        }

        function highlight(direction) {
            const items = Array.from(panel.querySelectorAll('.suggestion'));

            if (!items.length) {
                return;
            }

            activeIndex = (activeIndex + direction + items.length) % items.length;

            items.forEach(function (item, index) {
                item.classList.toggle('is-highlighted', index === activeIndex);
            });

            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        input.addEventListener('input', function () {
            const query = input.value.trim();

            window.clearTimeout(timer);

            if (controller) {
                controller.abort();
            }

            if (query.length < 2) {
                close();

                return;
            }

            timer = window.setTimeout(function () {
                controller = new AbortController();

                fetch('/api/search?q=' + encodeURIComponent(query) + '&limit=6', {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        if (input.value.trim() !== query) {
                            return;
                        }

                        render(query, (result.data && result.data.tracks) || []);
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            close();
                        }
                    });
            }, 280);
        });

        input.addEventListener('keydown', function (event) {
            if (panel.hidden) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                highlight(1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                highlight(-1);
            } else if (event.key === 'Escape') {
                close();
            } else if (event.key === 'Enter' && activeIndex >= 0) {
                const item = panel.querySelectorAll('.suggestion')[activeIndex];

                if (item) {
                    event.preventDefault();
                    window.location.href = item.getAttribute('href');
                }
            }
        });

        document.addEventListener('click', function (event) {
            if (!panel.contains(event.target) && !input.contains(event.target)) {
                close();
            }
        });

        input.form.addEventListener('submit', function () {
            close();
        });
    }

    /* --------------------------------------------------------------------- */
    /* Sidebar, scroll, shortcuts                                             */
    /* --------------------------------------------------------------------- */

    function initLayout() {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        const scrim = document.getElementById('sidebar-scrim');

        function openSidebar() {
            if (!sidebar || !scrim) {
                return;
            }

            sidebar.classList.add('is-open');
            scrim.hidden = false;
            document.body.classList.add('menu-open');
        }

        function closeSidebar() {
            if (!sidebar || !scrim) {
                return;
            }

            sidebar.classList.remove('is-open');
            scrim.hidden = true;
            document.body.classList.remove('menu-open');
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (sidebar.classList.contains('is-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        if (scrim) {
            scrim.addEventListener('click', closeSidebar);
        }

        if (sidebar) {
            sidebar.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', closeSidebar);
            });
        }

        const topbar = document.querySelector('.topbar');

        if (topbar) {
            const onScroll = function () {
                topbar.classList.toggle('is-scrolled', window.scrollY > 8);
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        document.addEventListener('keydown', function (event) {
            const target = event.target;
            const typing = target && (
                target.tagName === 'INPUT' ||
                target.tagName === 'TEXTAREA' ||
                target.tagName === 'SELECT' ||
                target.isContentEditable
            );

            if (event.key === 'Escape') {
                closeSidebar();
                closePlaylistModal();
            }

            if (event.key === '/' && !typing) {
                event.preventDefault();

                const input = document.getElementById('global-search');

                if (input) {
                    input.focus();
                    input.select();
                }
            }
        });
    }

    /* --------------------------------------------------------------------- */
    /* Global events                                                          */
    /* --------------------------------------------------------------------- */

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-theme-toggle]')) {
            event.preventDefault();
            toggleTheme();

            return;
        }

        const fav = event.target.closest('.fav-btn[data-fav]');

        if (fav) {
            event.preventDefault();
            event.stopPropagation();

            let track = null;

            try {
                track = JSON.parse(fav.dataset.fav);
            } catch (error) {
                return;
            }

            if (track && track.id) {
                toggleFavorite(track);
            }

            return;
        }

        const add = event.target.closest('[data-add-track]');

        if (add) {
            event.preventDefault();

            let track = null;

            try {
                track = JSON.parse(add.dataset.addTrack);
            } catch (error) {
                return;
            }

            if (track && track.id) {
                openPlaylistModal(track);
            }

            return;
        }

        const openCreate = event.target.closest('[data-open-create-playlist]');

        if (openCreate) {
            event.preventDefault();
            openPlaylistModal(null);

            return;
        }

        const modalAdd = event.target.closest('[data-playlist-add]');

        if (modalAdd) {
            event.preventDefault();
            addTrackToPlaylist(modalAdd.dataset.playlistAdd);

            return;
        }

        if (event.target.closest('[data-close-playlist-modal]')) {
            event.preventDefault();
            closePlaylistModal();

            return;
        }

        if (event.target.closest('[data-clear-history]')) {
            event.preventDefault();
            clearHistory();
        }
    });

    document.addEventListener('submit', function (event) {
        const form = event.target;

        if (!form || !form.dataset || !form.dataset.confirm) {
            return;
        }

        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });

    document.addEventListener('soundku:favorites-changed', function (event) {
        const detail = event.detail || {};

        if (detail.liked === false && detail.track) {
            removeLikedRow(detail.track);
        }
    });

    document.addEventListener('soundku:recent-changed', function () {
        // Home tetap server-rendered; riwayat baru muncul setelah refresh.
    });

    const recentClear = document.getElementById('recent-clear');

    if (recentClear) {
        recentClear.addEventListener('click', clearHistory);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('playlist-create-form');

        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const input = document.getElementById('playlist-create-name');
            const name = input ? input.value.trim() : '';

            if (!name) {
                toast('Nama playlist-nya diisi dulu ya.', 'error');

                if (input) {
                    input.focus();
                }

                return;
            }

            createPlaylist(name);
        });
    });

    /* --------------------------------------------------------------------- */
    /* Public API & init                                                      */
    /* --------------------------------------------------------------------- */

    SoundKu.escapeHtml = escapeHtml;
    SoundKu.toast = toast;
    SoundKu.formatDuration = formatDuration;
    SoundKu.renderTrackCard = renderTrackCard;
    SoundKu.renderTrackRow = renderTrackRow;
    SoundKu.openPlaylistModal = openPlaylistModal;
    SoundKu.favorites = {
        has: hasFavorite,
        toggle: toggleFavorite,
        sync: syncFavButtons,
        all: function () {
            return Array.from(likedIds);
        },
    };
    SoundKu.recent = {
        add: addRecent,
    };
    SoundKu.playlists = {
        all: function () {
            return playlists.slice();
        },
        create: createPlaylist,
    };

    initTheme();
    initSearch();
    initLayout();
    renderSidebarPlaylists();
    syncFavButtons();
})();
