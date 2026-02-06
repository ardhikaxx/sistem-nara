<?php

use App\Http\Controllers\AnalisisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnalisisController::class, 'index']);
Route::post('/analisis/import', [AnalisisController::class, 'import'])->name('analisis.import');
Route::post('/analisis/{analisis}/analyze', [AnalisisController::class, 'analyze'])->name('analisis.analyze');
Route::get('/analisis/history', [AnalisisController::class, 'history'])->name('analisis.history');
Route::get('/analisis/{analisis}/summary', [AnalisisController::class, 'summary'])->name('analisis.summary');
Route::get('/analisis/{analisis}/reviews', [AnalisisController::class, 'reviews'])->name('analisis.reviews');
Route::get('/analisis/{analisis}/export/csv', [AnalisisController::class, 'exportCsv'])->name('analisis.export.csv');
Route::get('/analisis/{analisis}/export/excel', [AnalisisController::class, 'exportExcel'])->name('analisis.export.excel');
Route::get('/analisis/model/status', [AnalisisController::class, 'modelStatus'])->name('analisis.model.status');
Route::post('/analisis/model/repair', [AnalisisController::class, 'repairModel'])->name('analisis.model.repair');
