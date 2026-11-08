/* ==========================================================================
   SoundKu — UI helpers, favorites, search suggestions
   ========================================================================== */

(function () {
    'use strict';

    const SoundKu = (window.SoundKu = window.SoundKu || {});

    const FAVORITES_KEY = 'soundku:favorites';
    const RECENT_KEY = 'soundku:recent';

    const PLAY_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4.5v15l13-7.5-13-7.5Z"/></svg>';
    const HEART_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>';
    const MUSIC_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>';

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

    function readJson(key, fallback) {
        try {
            const raw = window.localStorage.getItem(key);

            if (!raw) {
                return fallback;
            }

            const parsed = JSON.parse(raw);

            return parsed === null || parsed === undefined ? fallback : parsed;
        } catch (error) {
            return fallback;
        }
    }

    function writeJson(key, value) {
        try {
            window.localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            // Storage penuh atau diblokir: abaikan, fitur tetap jalan di sesi ini.
        }
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
    /* Favorites & recently played                                            */
    /* --------------------------------------------------------------------- */

    function favoriteTracks() {
        const tracks = readJson(FAVORITES_KEY, []);

        return Array.isArray(tracks) ? tracks.filter(function (track) {
            return track && track.id;
        }) : [];
    }

    function hasFavorite(track) {
        if (!track || !track.id) {
            return false;
        }

        return favoriteTracks().some(function (item) {
            return String(item.id) === String(track.id);
        });
    }

    function toggleFavorite(track) {
        let tracks = favoriteTracks();
        const exists = tracks.some(function (item) {
            return String(item.id) === String(track.id);
        });

        if (exists) {
            tracks = tracks.filter(function (item) {
                return String(item.id) !== String(track.id);
            });
        } else {
            tracks.unshift(track);
            tracks = tracks.slice(0, 200);
        }

        writeJson(FAVORITES_KEY, tracks);

        return !exists;
    }

    function recentTracks() {
        const tracks = readJson(RECENT_KEY, []);

        return Array.isArray(tracks) ? tracks.filter(function (track) {
            return track && track.id;
        }) : [];
    }

    function addRecent(track) {
        if (!track || !track.id) {
            return;
        }

        const tracks = recentTracks().filter(function (item) {
            return String(item.id) !== String(track.id);
        });

        tracks.unshift(track);
        writeJson(RECENT_KEY, tracks.slice(0, 12));
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
            button.setAttribute('aria-label', active ? 'Hapus dari favorit' : 'Tambahkan ke favorit');
        });
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
            '<article class="track-card">' +
                '<div class="track-art">' +
                    (track.artwork
                        ? '<img src="' + escapeHtml(track.artwork) + '" alt="" loading="lazy" decoding="async">'
                        : '<div class="art-placeholder">' + MUSIC_ICON + '</div>') +
                    '<button class="card-play" type="button" data-play-index="' + index + '" aria-label="Putar ' + escapeHtml(track.title) + '">' + PLAY_ICON + '</button>' +
                    (track.is_preview ? '<span class="preview-badge">30 detik</span>' : '') +
                    '<button class="fav-btn card-fav" type="button" data-fav="' + escapeHtml(JSON.stringify(track)) + '" data-fav-id="' + escapeHtml(track.id) + '" aria-label="Favorit" aria-pressed="false">' + HEART_ICON + '</button>' +
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
            '<div class="track-row">' +
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
                '<span class="row-genre">' + escapeHtml(track.genre || '—') + '</span>' +
                '<span class="row-duration">' + formatDuration(track.duration) + '</span>' +
                '<button class="fav-btn row-fav" type="button" data-fav="' + escapeHtml(JSON.stringify(track)) + '" data-fav-id="' + escapeHtml(track.id) + '" aria-label="Favorit" aria-pressed="false">' + HEART_ICON + '</button>' +
                '<a class="row-link" href="' + trackUrl(track) + '" aria-label="Detail">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>' +
                '</a>' +
            '</div>'
        );
    }

    /* --------------------------------------------------------------------- */
    /* Recently played section                                                */
    /* --------------------------------------------------------------------- */

    function renderRecentSection() {
        const section = document.getElementById('recent-section');
        const container = document.getElementById('recent-tracks');

        if (!section || !container) {
            return;
        }

        const tracks = recentTracks();
        section.hidden = tracks.length === 0;

        if (tracks.length === 0) {
            container.innerHTML = '';
            section.removeAttribute('data-queue');
            return;
        }

        section.dataset.queue = JSON.stringify(tracks);
        container.innerHTML = tracks.map(renderTrackCard).join('');
        syncFavButtons(container);
    }

    /* --------------------------------------------------------------------- */
    /* Favorites page                                                         */
    /* --------------------------------------------------------------------- */

    function renderFavoritesPage() {
        const list = document.getElementById('favorites-list');

        if (!list) {
            return;
        }

        const empty = document.getElementById('favorites-empty');
        const playAll = document.getElementById('favorites-play-all');
        const tracks = favoriteTracks();

        list.dataset.queue = JSON.stringify(tracks);
        list.innerHTML = tracks.map(renderTrackRow).join('');

        if (empty) {
            empty.hidden = tracks.length > 0;
        }

        if (playAll) {
            playAll.hidden = tracks.length === 0;
        }

        syncFavButtons(list);
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
        const button = event.target.closest('.fav-btn[data-fav]');

        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        let track = null;

        try {
            track = JSON.parse(button.dataset.fav);
        } catch (error) {
            return;
        }

        if (!track || !track.id) {
            return;
        }

        const active = toggleFavorite(track);

        syncFavButtons();
        toast(active ? 'Ditambahkan ke favorit.' : 'Dihapus dari favorit.', active ? 'success' : 'info');
        document.dispatchEvent(new CustomEvent('soundku:favorites-changed'));
    });

    document.addEventListener('soundku:favorites-changed', function () {
        renderFavoritesPage();
    });

    document.addEventListener('soundku:recent-changed', renderRecentSection);

    const recentClear = document.getElementById('recent-clear');

    if (recentClear) {
        recentClear.addEventListener('click', function () {
            writeJson(RECENT_KEY, []);
            renderRecentSection();
            toast('Riwayat putar dibersihkan.');
        });
    }

    const favoritesPlayAll = document.getElementById('favorites-play-all');

    if (favoritesPlayAll) {
        favoritesPlayAll.addEventListener('click', function () {
            const tracks = favoriteTracks();

            if (tracks.length && SoundKu.player) {
                SoundKu.player.playQueue(tracks, 0);
            }
        });
    }

    /* --------------------------------------------------------------------- */
    /* Public API & init                                                      */
    /* --------------------------------------------------------------------- */

    SoundKu.escapeHtml = escapeHtml;
    SoundKu.toast = toast;
    SoundKu.formatDuration = formatDuration;
    SoundKu.renderTrackCard = renderTrackCard;
    SoundKu.renderTrackRow = renderTrackRow;
    SoundKu.favorites = {
        all: favoriteTracks,
        has: hasFavorite,
        toggle: toggleFavorite,
        sync: syncFavButtons,
    };
    SoundKu.recent = {
        all: recentTracks,
        add: addRecent,
    };

    initSearch();
    initLayout();
    renderRecentSection();
    renderFavoritesPage();
    syncFavButtons();
})();
