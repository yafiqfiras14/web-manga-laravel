<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ChapterController as AdminChapterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MangaController as AdminMangaController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MangaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/manga/{slug}', [MangaController::class, 'show'])->name('manga.show');
Route::get('/manga/{slug}/chapter/{number}', [ChapterController::class, 'show'])->name('chapter.show');

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.attempt');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('manga', AdminMangaController::class)->except('show');

    Route::get('/manga/{manga}/chapters/create', [AdminChapterController::class, 'create'])->name('chapters.create');
    Route::post('/manga/{manga}/chapters', [AdminChapterController::class, 'store'])->name('chapters.store');
    Route::delete('/manga/{manga}/chapters/{chapter}', [AdminChapterController::class, 'destroy'])->name('chapters.destroy');
});
