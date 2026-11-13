<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeaturedController;
use App\Http\Controllers\Api\LibraryController as LibraryApiController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UserPlaylistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal akun (guest)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [UserAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [UserAuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [UserAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [UserAuthController::class, 'register'])->name('register.attempt');
});

/*
|--------------------------------------------------------------------------
| Situs utama (wajib login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/genre/{genre}', [GenreController::class, 'show'])->name('genre.show');
    Route::get('/track/{id}', [TrackController::class, 'show'])->name('track.show');
    Route::get('/artist/{id}', [ArtistController::class, 'show'])->name('artist.show');
    Route::get('/playlist/{id}', [PlaylistController::class, 'show'])->name('playlist.show');

    Route::redirect('/favorites', '/library/liked')->name('favorites');

    Route::get('/library', [LibraryController::class, 'index'])->name('library');
    Route::get('/library/liked', [LibraryController::class, 'liked'])->name('library.liked');
    Route::get('/library/history', [LibraryController::class, 'history'])->name('library.history');
    Route::get('/account', [LibraryController::class, 'account'])->name('account');

    Route::post('/library/playlists', [UserPlaylistController::class, 'store'])->name('library.playlists.store');
    Route::get('/library/playlists/{playlist}', [UserPlaylistController::class, 'show'])->name('library.playlists.show');
    Route::put('/library/playlists/{playlist}', [UserPlaylistController::class, 'update'])->name('library.playlists.update');
    Route::delete('/library/playlists/{playlist}', [UserPlaylistController::class, 'destroy'])->name('library.playlists.destroy');
    Route::post('/library/playlists/{playlist}/tracks', [UserPlaylistController::class, 'addTrack'])->name('library.playlists.tracks.store');
    Route::delete('/library/playlists/{playlist}/tracks/{track}', [UserPlaylistController::class, 'removeTrack'])->name('library.playlists.tracks.destroy');

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/search', [MusicController::class, 'search'])->name('search');
        Route::get('/track/{id}', [MusicController::class, 'track'])->name('track');
        Route::get('/stream/{id}', [MusicController::class, 'stream'])->name('stream');
        Route::get('/hls/{id}', [MusicController::class, 'hls'])->name('hls');
        Route::get('/segment', [MusicController::class, 'segment'])->name('segment');

        Route::post('/library/like', [LibraryApiController::class, 'toggleLike'])->name('library.like');
        Route::post('/library/history', [LibraryApiController::class, 'storeHistory'])->name('library.history.store');
        Route::delete('/library/history', [LibraryApiController::class, 'clearHistory'])->name('library.history.clear');
        Route::get('/library/playlists', [LibraryApiController::class, 'playlists'])->name('library.playlists');
    });
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.attempt');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/featured', [FeaturedController::class, 'index'])->name('featured.index');
    Route::get('/featured/create', [FeaturedController::class, 'create'])->name('featured.create');
    Route::post('/featured', [FeaturedController::class, 'store'])->name('featured.store');
    Route::get('/featured/{featured}/edit', [FeaturedController::class, 'edit'])->name('featured.edit');
    Route::put('/featured/{featured}', [FeaturedController::class, 'update'])->name('featured.update');
    Route::patch('/featured/{featured}/toggle', [FeaturedController::class, 'toggle'])->name('featured.toggle');
    Route::delete('/featured/{featured}', [FeaturedController::class, 'destroy'])->name('featured.destroy');
});
