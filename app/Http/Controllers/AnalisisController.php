<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use App\Models\DataUlasan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Response;

use App\Exports\AnalisisUlasanExport;

class AnalisisController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return response()->json(['message' => 'File tidak dapat dibuka.'], 422);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return response()->json(['message' => 'File CSV kosong atau tidak valid.'], 422);
        }

        $normalizedHeader = array_map(static function ($value) {
            return strtolower(trim($value));
        }, $header);
        $index = array_flip($normalizedHeader);

        if (!array_key_exists('review_content', $index)) {
            fclose($handle);
            return response()->json(['message' => 'Kolom "review_content" tidak ditemukan.'], 422);
        }

        $analysisDate = Carbon::now()->toDateString();
        $analysis = null;
        $preview = [];
        $totalRows = 0;

        try {
            DB::transaction(function () use (&$analysis, $analysisDate, $handle, $index, &$preview, &$totalRows) {
                $analysis = Analisis::create([
                    'nama_analisis' => $this->generateAnalysisName(),
                    'tanggal_analisis' => $analysisDate,
                ]);

                $batch = [];
                $previewLimit = 6;

                while (($row = fgetcsv($handle)) !== false) {
                    $reviewContent = $row[$index['review_content']] ?? null;
                    if ($reviewContent === null || trim($reviewContent) === '') {
                        continue;
                    }

                    $reviewDate = $this->parseDateTime($row, $index, 'review_date');
                    $replyDate = $this->parseDateTime($row, $index, 'reply_date');

                    $batch[] = [
                        'analisis_id' => $analysis->id,
                        'review_id' => $row[$index['review_id']] ?? null,
                        'user_name' => $row[$index['user_name']] ?? null,
                        'user_image' => $row[$index['user_image']] ?? null,
                        'rating' => $this->parseInteger($row, $index, 'rating'),
                        'review_content' => $reviewContent,
                        'review_date' => $reviewDate,
                        'thumbs_up' => $this->parseInteger($row, $index, 'thumbs_up'),
                        'reply_content' => $row[$index['reply_content']] ?? null,
                        'reply_date' => $replyDate,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $totalRows++;
                    if (count($preview) < $previewLimit) {
                        $preview[] = [
                            'review_content' => $reviewContent,
                            'sentiment' => null,
                        ];
                    }

                    if (count($batch) >= 500) {
                        DataUlasan::insert($batch);
                        $batch = [];
                    }
                }

                if ($batch) {
                    DataUlasan::insert($batch);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Import analisis gagal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            fclose($handle);

            return response()->json([
                'message' => 'Terjadi kesalahan saat mengimpor data.',
            ], 500);
        }

        fclose($handle);

        if (!$analysis) {
            return response()->json([
                'message' => 'Gagal membuat data analisis.',
            ], 500);
        }

        return response()->json([
            'analysis_id' => $analysis->id,
            'analysis_name' => $analysis->nama_analisis,
            'total_reviews' => $totalRows,
            'preview' => $preview,
            'message' => 'Data berhasil diimport ke database.',
        ]);
    }

    public function analyze(Analisis $analisis): JsonResponse
    {
        $start = microtime(true);

        $missing = $this->missingModelFiles();
        if ($missing) {
            Log::warning('Model analisis tidak lengkap', [
                'missing' => $missing,
            ]);
            return response()->json([
                'message' => 'File model tidak lengkap. Silakan perbaiki model terlebih dahulu.',
                'missing' => $missing,
            ], 422);
        }

        $reviews = DataUlasan::where('analisis_id', $analisis->id)
            ->select('id', 'review_content')
            ->get();

        if ($reviews->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data ulasan untuk dianalisis.'], 422);
        }

        $payload = [
            'items' => $reviews->map(static function ($review) {
                return [
                    'id' => $review->id,
                    'text' => $review->review_content,
                ];
            })->all(),
        ];

        $scriptPath = base_path('scripts/analysis.py');
        $process = new Process(['python', $scriptPath]);
        $process->setInput(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            Log::error('Analisis gagal dijalankan', [
                'analysis_id' => $analisis->id,
                'analysis_name' => $analisis->nama_analisis,
                'error_output' => $errorOutput,
                'output' => $process->getOutput(),
            ]);
            return response()->json([
                'message' => $errorOutput !== '' ? $errorOutput : 'Gagal menjalankan analisis.',
                'error' => $errorOutput,
            ], 500);
        }

        $output = trim($process->getOutput());
        $data = json_decode($output, true);

        if (!is_array($data) || !isset($data['results'])) {
            Log::error('Output analisis tidak valid', [
                'analysis_id' => $analisis->id,
                'analysis_name' => $analisis->nama_analisis,
                'output' => $output,
            ]);
            return response()->json([
                'message' => 'Output analisis tidak valid.',
                'output' => $output,
            ], 500);
        }

        $results = collect($data['results'])->keyBy('id');
        $updates = [];

        foreach ($results as $id => $result) {
            $updates[] = [
                'id' => (int) $id,
                'sentiment' => $result['sentiment'] ?? null,
                'confidence' => $result['confidence'] ?? null,
            ];
        }

        foreach (array_chunk($updates, 500) as $chunk) {
            DataUlasan::upsert($chunk, ['id'], ['sentiment', 'confidence']);
        }

        $counts = DataUlasan::where('analisis_id', $analisis->id)
            ->select('sentiment', DB::raw('count(*) as total'))
            ->groupBy('sentiment')
            ->pluck('total', 'sentiment');

        $positive = (int) ($counts['positive'] ?? 0);
        $negative = (int) ($counts['negative'] ?? 0);
        $neutral = (int) ($counts['neutral'] ?? 0);
        $total = $positive + $negative + $neutral;

        $analisis->update([
            'total_review_positif' => $positive,
            'total_review_netral' => $neutral,
            'total_review_negatif' => $negative,
        ]);

        $averageConfidence = DataUlasan::where('analisis_id', $analisis->id)->avg('confidence');
        $processingTime = microtime(true) - $start;

        $sampleReviews = DataUlasan::where('analisis_id', $analisis->id)
            ->select('review_content', 'sentiment')
            ->limit(10)
            ->get();

        Log::info('Analisis selesai', [
            'analysis_id' => $analisis->id,
            'analysis_name' => $analisis->nama_analisis,
            'total' => $total,
            'positive' => $positive,
            'negative' => $negative,
            'neutral' => $neutral,
            'average_confidence' => $averageConfidence,
            'processing_time' => $processingTime,
        ]);

        return response()->json([
            'analysis_id' => $analisis->id,
            'analysis_name' => $analisis->nama_analisis,
            'total' => $total,
            'positive' => $positive,
            'negative' => $negative,
            'neutral' => $neutral,
            'model_accuracy' => $data['model_accuracy'] ?? null,
            'average_confidence' => $averageConfidence,
            'processing_time' => $processingTime,
            'reviews' => $sampleReviews,
        ]);
    }

    public function summary(Analisis $analisis): JsonResponse
    {
        $positive = (int) ($analisis->total_review_positif ?? 0);
        $negative = (int) ($analisis->total_review_negatif ?? 0);
        $neutral = (int) ($analisis->total_review_netral ?? 0);
        $total = $positive + $negative + $neutral;

        if ($total === 0) {
            return response()->json([
                'message' => 'Analisis belum dijalankan.',
            ], 422);
        }

        $averageConfidence = DataUlasan::where('analisis_id', $analisis->id)->avg('confidence');

        $sampleReviews = DataUlasan::where('analisis_id', $analisis->id)
            ->select('review_content', 'sentiment')
            ->limit(10)
            ->get();

        return response()->json([
            'analysis_id' => $analisis->id,
            'analysis_name' => $analisis->nama_analisis,
            'total' => $total,
            'positive' => $positive,
            'negative' => $negative,
            'neutral' => $neutral,
            'average_confidence' => $averageConfidence,
            'reviews' => $sampleReviews,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $perPage = 10;
        $history = Analisis::query()
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json($history);
    }

    public function reviews(Analisis $analisis, Request $request): JsonResponse
    {
        $perPage = 10;
        $reviews = DataUlasan::query()
            ->where('analisis_id', $analisis->id)
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json($reviews);
    }

    public function exportCsv(Analisis $analisis)
    {
        if ($analisis->total_review_positif === null
            || $analisis->total_review_negatif === null
            || $analisis->total_review_netral === null) {
            return response()->json([
                'message' => 'Analisis belum dijalankan. Silakan jalankan analisis terlebih dahulu.',
            ], 422);
        }

        $filename = $analisis->nama_analisis . '_ulasan.csv';
        $query = DataUlasan::query()
            ->where('analisis_id', $analisis->id)
            ->orderBy('id');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'review_id',
                'user_name',
                'user_image',
                'rating',
                'review_content',
                'review_date',
                'thumbs_up',
                'reply_content',
                'reply_date',
                'sentiment',
                'confidence',
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->review_id,
                        $row->user_name,
                        $row->user_image,
                        $row->rating,
                        $row->review_content,
                        $row->review_date?->toDateTimeString(),
                        $row->thumbs_up,
                        $row->reply_content,
                        $row->reply_date?->toDateTimeString(),
                        $row->sentiment,
                        $row->confidence,
                    ]);
                }
            });

            fclose($handle);
        };

        return Response::streamDownload($callback, $filename, $headers);
    }

    public function exportExcel(Analisis $analisis)
    {
        $excelClass = \Maatwebsite\Excel\Facades\Excel::class;
        if (!class_exists($excelClass)) {
            return response()->json([
                'message' => 'Paket Excel belum terpasang. Jalankan: composer require maatwebsite/excel',
            ], 500);
        }

        if ($analisis->total_review_positif === null
            || $analisis->total_review_negatif === null
            || $analisis->total_review_netral === null) {
            return response()->json([
                'message' => 'Analisis belum dijalankan. Silakan jalankan analisis terlebih dahulu.',
            ], 422);
        }

        $filename = $analisis->nama_analisis . '_ulasan.xlsx';

        return $excelClass::download(new AnalisisUlasanExport($analisis->id), $filename);
    }

    public function modelStatus(): JsonResponse
    {
        $missing = $this->missingModelFiles();

        return response()->json([
            'ok' => empty($missing),
            'missing' => $missing,
        ]);
    }

    public function repairModel(): JsonResponse
    {
        $backupDir = base_path('scripts/oss-analisis-backup');
        $targetDir = base_path('scripts/oss-analisis');

        if (!File::exists($backupDir)) {
            return response()->json([
                'message' => 'Backup model tidak ditemukan.',
            ], 404);
        }

        File::ensureDirectoryExists($targetDir);
        File::copyDirectory($backupDir, $targetDir);

        $missing = $this->missingModelFiles();
        if ($missing) {
            return response()->json([
                'message' => 'Perbaikan model belum lengkap.',
                'missing' => $missing,
            ], 422);
        }

        return response()->json([
            'message' => 'Model berhasil diperbaiki.',
        ]);
    }

    private function generateAnalysisName(): string
    {
        $last = Analisis::orderByDesc('id')->first();
        $lastNumber = 0;

        if ($last && preg_match('/A-(\\d+)/', $last->nama_analisis, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = $lastNumber + 1;

        return 'A-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function parseInteger(array $row, array $index, string $key): ?int
    {
        if (!array_key_exists($key, $index)) {
            return null;
        }

        $value = trim((string) ($row[$index[$key]] ?? ''));
        if ($value === '') {
            return null;
        }

        return (int) $value;
    }

    private function parseDateTime(array $row, array $index, string $key): ?string
    {
        if (!array_key_exists($key, $index)) {
            return null;
        }

        $value = trim((string) ($row[$index[$key]] ?? ''));
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function missingModelFiles(): array
    {
        $base = base_path('scripts/oss-analisis');
        $files = [
            $base . DIRECTORY_SEPARATOR . 'config.py',
            $base . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'predict.py',
            $base . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'preprocess.py',
            $base . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'naive_bayes.py',
            $base . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'naive_bayes_model.pkl',
            $base . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'vectorizer.pkl',
            $base . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'label_encoder.pkl',
        ];

        $missing = [];
        foreach ($files as $file) {
            if (!File::exists($file)) {
                $missing[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
            }
        }

        return $missing;
    }
}
