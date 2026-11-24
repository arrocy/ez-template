<?php

use Arrocy\Template\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('template')->name('template.')->group(function (): void {
    Route::get('/', [TemplateController::class, 'index'])->name('index');
    Route::post('/', [TemplateController::class, 'store'])->name('store');
    Route::post('/import', [TemplateController::class, 'import'])->name('import');
    Route::get('/export', [TemplateController::class, 'export'])->name('export');
    Route::get('/{template}/edit', [TemplateController::class, 'edit'])->name('edit');
    Route::put('/{template}', [TemplateController::class, 'update'])->name('update');
    Route::get('/{template}', [TemplateController::class, 'toggle'])->name('toggle');
    Route::delete('/{template}', [TemplateController::class, 'destroy'])->name('destroy');
    Route::delete('/', [TemplateController::class, 'bulkDelete'])->name('bulk-destroy');
});