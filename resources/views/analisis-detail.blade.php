<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Analisis - {{ $analisis->nama_analisis }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; }
        .card { border-radius: 14px; }
        .stat { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
        .stat h3 { font-size: 1.5rem; margin: 0; }
        .badge-positive { background: #d1fae5; color: #047857; }
        .badge-negative { background: #fee2e2; color: #b91c1c; }
        .badge-neutral { background: #fef3c7; color: #b45309; }
        .badge-soft { padding: 6px 10px; border-radius: 999px; font-weight: 600; font-size: 0.8rem; }
        .pagination-wrap { display: flex; justify-content: center; }
        .pagination { gap: 6px; }
        .page-link {
            border-radius: 999px !important;
            border: 1px solid #e2e8f0;
            color: #334155;
            padding: 8px 14px;
            box-shadow: none;
            transition: all 0.15s ease;
        }
        .page-link:hover {
            background: #eef2ff;
            color: #1d4ed8;
            border-color: #c7d2fe;
        }
        .page-item.active .page-link {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(29, 78, 216, 0.25);
            transform: translateY(-1px);
        }
        .page-item.disabled .page-link {
            background: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
        }
        .pagination-meta {
            color: #64748b;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 10px;
        }
        @media (max-width: 576px) {
            .page-link { padding: 6px 10px; font-size: 0.85rem; }
            .page-item.hide-mobile { display: none; }
            .pagination { gap: 4px; }
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h2 class="mb-1">Detail Analisis {{ $analisis->nama_analisis }}</h2>
            <div class="text-muted">Tanggal analisis: {{ $analisis->tanggal_analisis }}</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ url('/') }}">Kembali</a>
            <a class="btn btn-outline-primary {{ $isAnalyzed ? '' : 'disabled' }}" href="{{ $isAnalyzed ? url('/analisis/'.$analisis->id.'/export/csv') : '#' }}">CSV</a>
            <a class="btn btn-outline-success {{ $isAnalyzed ? '' : 'disabled' }}" href="{{ $isAnalyzed ? url('/analisis/'.$analisis->id.'/export/excel') : '#' }}">Excel</a>
        </div>
    </div>

    @if(!$isAnalyzed)
        <div class="alert alert-warning">Analisis belum dijalankan. Silakan jalankan analisis terlebih dahulu.</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat">
                <div class="text-muted">Total Review</div>
                <h3>{{ $totalReviews }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat">
                <div class="text-muted">Positif</div>
                <h3 class="text-success">{{ $positive }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat">
                <div class="text-muted">Netral</div>
                <h3 class="text-warning">{{ $neutral }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat">
                <div class="text-muted">Negatif</div>
                <h3 class="text-danger">{{ $negative }}</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat">
                <div class="text-muted">Total Dianalisis</div>
                <h3>{{ $totalAnalyzed }}</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat">
                <div class="text-muted">Rata-rata Confidence</div>
                <h3>{{ $averageConfidence ? number_format($averageConfidence * 100, 2) : '0.00' }}%</h3>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <strong>Data Ulasan</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Review</th>
                        <th style="width: 120px;">Rating</th>
                        <th style="width: 120px;">Sentimen</th>
                        <th style="width: 140px;">Confidence</th>
                        <th style="width: 140px;">Tanggal</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($reviews as $review)
                        @php
                            $sentiment = $review->sentiment ?? 'neutral';
                            $badgeClass = $sentiment === 'positive' ? 'badge-positive'
                                : ($sentiment === 'negative' ? 'badge-negative' : 'badge-neutral');
                        @endphp
                        <tr>
                            <td>{{ $review->id }}</td>
                            <td>{{ $review->review_content }}</td>
                            <td>{{ $review->rating ?? '-' }}</td>
                            <td>
                                <span class="badge-soft {{ $badgeClass }}">
                                    {{ $sentiment === 'positive' ? 'Positif' : ($sentiment === 'negative' ? 'Negatif' : 'Netral') }}
                                </span>
                            </td>
                            <td>{{ $review->confidence !== null ? number_format($review->confidence * 100, 2) . '%' : '-' }}</td>
                            <td>{{ $review->review_date ? $review->review_date->format('Y-m-d') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data ulasan.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="pagination-meta">
                Menampilkan {{ $reviews->firstItem() ?? 0 }}–{{ $reviews->lastItem() ?? 0 }} dari {{ $reviews->total() }} data
            </div>
            <div class="pagination-wrap">
                {{ $reviews->onEachSide(1)->links('pagination.analysis-detail') }}
            </div>
        </div>
    </div>
</div>
</body>
</html>
