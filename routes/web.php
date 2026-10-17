<?php

use App\Http\Controllers\LibraryEntryController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', 'library')->name('dashboard');

    Route::get('search', SearchController::class)->name('search');

    Route::get('library', [LibraryEntryController::class, 'index'])->name('library.index');
    Route::post('library', [LibraryEntryController::class, 'store'])->name('library.store');
    Route::patch('library/{entry}', [LibraryEntryController::class, 'update'])->name('library.update');
    Route::delete('library/{entry}', [LibraryEntryController::class, 'destroy'])->name('library.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
