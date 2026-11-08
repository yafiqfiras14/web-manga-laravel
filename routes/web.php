<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeaturedController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/genre/{genre}', [GenreController::class, 'show'])->name('genre.show');
Route::get('/track/{id}', [TrackController::class, 'show'])->name('track.show');
Route::get('/artist/{id}', [ArtistController::class, 'show'])->name('artist.show');
Route::get('/playlist/{id}', [PlaylistController::class, 'show'])->name('playlist.show');
Route::view('/favorites', 'favorites')->name('favorites');

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/search', [MusicController::class, 'search'])->name('search');
    Route::get('/track/{id}', [MusicController::class, 'track'])->name('track');
    Route::get('/stream/{id}', [MusicController::class, 'stream'])->name('stream');
    Route::get('/hls/{id}', [MusicController::class, 'hls'])->name('hls');
    Route::get('/segment', [MusicController::class, 'segment'])->name('segment');
});

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.attempt');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
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
