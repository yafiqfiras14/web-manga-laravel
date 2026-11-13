<div class="playlist-modal" id="playlist-modal" hidden>
    <div class="playlist-modal-scrim" data-close-playlist-modal></div>
    <div class="playlist-modal-card" role="dialog" aria-modal="true" aria-labelledby="playlist-modal-title">
        <header class="playlist-modal-head">
            <div>
                <span class="eyebrow"><x-icon name="queue" :size="13" /> Simpan musik</span>
                <h2 id="playlist-modal-title">Simpan ke playlist</h2>
                <p id="playlist-modal-sub">Pilih playlist tujuan atau bikin yang baru.</p>
            </div>
            <button class="icon-btn" type="button" data-close-playlist-modal aria-label="Tutup">
                <x-icon name="close" :size="18" />
            </button>
        </header>

        <div class="playlist-modal-track" id="playlist-modal-track" hidden>
            <div class="playlist-modal-art" id="playlist-modal-art"></div>
            <div class="playlist-modal-meta">
                <strong id="playlist-modal-track-title"></strong>
                <span id="playlist-modal-track-artist"></span>
            </div>
        </div>

        <div class="playlist-modal-list" id="playlist-modal-list"></div>

        <form class="playlist-create-form" id="playlist-create-form" autocomplete="off">
            <input type="text" name="name" id="playlist-create-name" maxlength="80" required
                   placeholder="Nama playlist baru" aria-label="Nama playlist baru">
            <button type="submit" class="btn btn-primary btn-sm">
                <x-icon name="plus" :size="14" /> Buat
            </button>
        </form>

        <p class="playlist-modal-note" id="playlist-modal-note">
            Playlist bisa diganti nama atau dihapus kapan saja dari halaman Perpustakaan.
        </p>
    </div>
</div>
