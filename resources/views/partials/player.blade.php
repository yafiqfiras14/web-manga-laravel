<div class="player" id="player" data-player>
    <div class="player-track">
        <div class="player-art" id="player-art">
            <x-icon name="music" :size="22" />
        </div>
        <div class="player-meta">
            <a class="player-title" id="player-title" href="#" hidden>Belum ada lagu</a>
            <span class="player-artist" id="player-artist">Pilih lagu untuk mulai</span>
        </div>
        <button class="fav-btn player-fav" id="player-fav" type="button" disabled
                aria-label="Favoritkan lagu ini" aria-pressed="false">
            <x-icon name="heart" :size="18" />
        </button>
    </div>

    <div class="player-center">
        <div class="player-controls">
            <button class="player-btn" type="button" data-action="shuffle" aria-label="Acak" title="Acak">
                <x-icon name="shuffle" :size="17" />
            </button>
            <button class="player-btn" type="button" data-action="prev" aria-label="Lagu sebelumnya" title="Sebelumnya">
                <x-icon name="skip-back" :size="19" />
            </button>
            <button class="player-btn play-toggle" id="play-toggle" type="button" data-action="toggle"
                    aria-label="Putar" title="Putar / jeda" disabled>
                <x-icon name="play" :size="21" id="play-icon" />
                <span class="player-spinner" id="player-spinner" hidden></span>
            </button>
            <button class="player-btn" type="button" data-action="next" aria-label="Lagu berikutnya" title="Berikutnya">
                <x-icon name="skip-forward" :size="19" />
            </button>
            <button class="player-btn" id="repeat-btn" type="button" data-action="repeat"
                    aria-label="Ulangi" title="Ulangi">
                <x-icon name="repeat" :size="17" id="repeat-icon" />
            </button>
        </div>

        <div class="player-progress">
            <span id="time-current">0:00</span>
            <input type="range" id="seek" class="range" min="0" max="100" value="0" step="0.1"
                   aria-label="Posisi lagu" disabled>
            <span id="time-duration">0:00</span>
        </div>
    </div>

    <div class="player-actions">
        <span class="preview-pill" id="preview-pill" hidden>Preview</span>
        <button class="player-btn queue-toggle" id="queue-toggle" type="button" aria-label="Buka antrean" title="Antrean">
            <x-icon name="queue" :size="19" />
            <span class="queue-badge" id="queue-badge" hidden>0</span>
        </button>
        <div class="volume-control">
            <button class="player-btn" id="mute-toggle" type="button" data-action="mute"
                    aria-label="Bisukan" title="Bisukan">
                <x-icon name="volume" :size="19" id="volume-icon" />
            </button>
            <input type="range" id="volume" class="range volume-range" min="0" max="1" step="0.01"
                   value="0.8" aria-label="Volume">
        </div>
    </div>

    <audio id="audio" preload="metadata"></audio>
</div>

<div class="drawer-scrim" id="drawer-scrim" hidden></div>

<aside class="queue-drawer" id="queue-drawer" aria-hidden="true" aria-label="Antrean lagu">
    <header class="queue-header">
        <div>
            <h2>Antrean</h2>
            <p id="queue-summary">Belum ada lagu</p>
        </div>
        <button class="icon-btn" id="queue-close" type="button" aria-label="Tutup antrean">
            <x-icon name="close" :size="19" />
        </button>
    </header>

    <div class="queue-list" id="queue-list">
        <p class="queue-empty">Antrean masih kosong. Tekan tombol putar di salah satu lagu.</p>
    </div>

    <footer class="queue-footer">
        <button class="btn btn-outline btn-block" id="queue-clear" type="button">
            <x-icon name="trash" :size="15" /> Bersihkan antrean
        </button>
    </footer>
</aside>

<div class="toasts" id="toasts" aria-live="polite" aria-atomic="true"></div>
