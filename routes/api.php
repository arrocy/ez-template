<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->prefix('api/template')->name('template.api.')->group(function (): void {
    // Add package API routes here.
});