<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Analisis - Sistem NARA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --primary: #1d4ed8;
            --border: #e2e8f0;
            --card: #ffffff;
            --shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            --radius: 16px;
        }
        body {
            background: radial-gradient(circle at 10% 0%, #eef2ff 0%, #f8fafc 40%, #f1f5f9 100%);
            color: var(--ink);
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-hero {
            background: linear-gradient(135deg, #1d4ed8, #2563eb 45%, #60a5fa);
            color: #fff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--shadow);
        }
        .hero-stats {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .hero-stat {
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .hero-badge {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 600;
        }
        .pill-actions .btn {
            border-radius: 999px;
            font-weight: 600;
            padding: 8px 16px;
        }
        .pill-actions .btn i { margin-right: 6px; }
        .btn-ghost {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
        }
        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }
        .card {
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin: 18px 0 6px;
        }
        .summary-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 16px;
        }
        .summary-card h4 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
        }
        .summary-card small { color: var(--muted); font-weight: 600; }
        .table-wrap { border-radius: 16px; overflow: hidden; }
        .table thead th {
            background: #f8fafc;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            color: var(--muted);
        }
        .table-hover tbody tr:hover { background: #f8fafc; }
        .action-row .btn { border-radius: 999px; font-weight: 600; }
        .action-row {
            gap: 8px;
        }
        .action-row .btn {
            padding: 6px 12px;
            font-size: 0.82rem;
        }
        .action-row .btn-outline-primary {
            border-color: #c7d2fe;
            color: #1d4ed8;
        }
        .action-row .btn-outline-primary:hover {
            background: #eef2ff;
        }
        .action-row .btn-outline-success {
            border-color: #bbf7d0;
            color: #15803d;
        }
        .action-row .btn-outline-success:hover {
            background: #dcfce7;
        }
        .action-row .btn-outline-secondary {
            border-color: #e2e8f0;
            color: #475569;
        }
        .action-row .btn-outline-secondary:hover {
            background: #f8fafc;
        }
        .status-pill {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-pill.success { background: #dcfce7; color: #15803d; }
        .status-pill.pending { background: #fef3c7; color: #b45309; }
        .pagination-wrap { display: flex; justify-content: center; }
        .page-link {
            border-radius: 999px !important;
            border: 1px solid var(--border);
            color: #334155;
            padding: 8px 14px;
        }
        .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .filter-pill {
            border-radius: 999px;
            border: 1px solid var(--border);
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--muted);
            background: #fff;
            text-decoration: none;
        }
        .filter-pill.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="page-hero mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="mb-2">Riwayat Analisis</h2>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="hero-badge">Total: {{ $totalAll }}</span>
                    <span class="hero-badge">Pagination: 10 data</span>
                    <span class="hero-badge">Selesai: {{ $totalDone }}</span>
                    <span class="hero-badge">Belum: {{ $totalPending }}</span>
                </div>
            </div>
            <div class="d-flex gap-2 pill-actions">
                <a class="btn btn-ghost" href="{{ url('/') }}"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a class="btn btn-outline-light {{ empty($status) ? 'disabled' : '' }}" href="{{ url('/riwayat-analisis') }}">
                    <i class="fas fa-rotate-left"></i> Reset Filter
                </a>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap mt-3">
            <a class="filter-pill {{ empty($status) ? 'active' : '' }}" href="{{ url('/riwayat-analisis') }}">Semua</a>
            <a class="filter-pill {{ $status === 'selesai' ? 'active' : '' }}" href="{{ url('/riwayat-analisis?status=selesai') }}">Selesai</a>
            <a class="filter-pill {{ $status === 'belum' ? 'active' : '' }}" href="{{ url('/riwayat-analisis?status=belum') }}">Belum Analisis</a>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <small>Total Analisis</small>
            <h4>{{ $history->total() }}</h4>
        </div>
        <div class="summary-card">
            <small>Halaman Aktif</small>
            <h4>{{ $history->currentPage() }}</h4>
        </div>
        <div class="summary-card">
            <small>Per Halaman</small>
            <h4>{{ $history->perPage() }}</h4>
        </div>
        <div class="summary-card">
            <small>Filter</small>
            <h4>{{ $status === 'selesai' ? 'Selesai' : ($status === 'belum' ? 'Belum' : 'Semua') }}</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0 table-wrap">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Kode</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Positif</th>
                        <th>Netral</th>
                        <th>Negatif</th>
                        <th>Status</th>
                        <th style="width: 260px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($history as $item)
                        @php
                            $total = ($item->total_review_positif ?? 0) + ($item->total_review_netral ?? 0) + ($item->total_review_negatif ?? 0);
                            $isAnalyzed = $item->total_review_positif !== null
                                && $item->total_review_netral !== null
                                && $item->total_review_negatif !== null;
                        @endphp
                        <tr>
                            <td><strong>{{ $item->nama_analisis }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal_analisis)->locale('id')->translatedFormat('d M Y') }}</td>
                            <td>{{ $total ?: '-' }}</td>
                            <td>{{ $item->total_review_positif ?? '-' }}</td>
                            <td>{{ $item->total_review_netral ?? '-' }}</td>
                            <td>{{ $item->total_review_negatif ?? '-' }}</td>
                            <td>
                                <span class="status-pill {{ $isAnalyzed ? 'success' : 'pending' }}">
                                    {{ $isAnalyzed ? 'Selesai' : 'Belum Analisis' }}
                                </span>
                            </td>
                            <td class="d-flex gap-2 flex-wrap action-row">
                                <a class="btn btn-outline-primary btn-sm" href="{{ url('/analisis/'.$item->id) }}">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a class="btn btn-outline-secondary btn-sm {{ $isAnalyzed ? '' : 'disabled' }}"
                                   href="{{ $isAnalyzed ? url('/analisis/'.$item->id.'/export/csv') : '#' }}">
                                    <i class="fas fa-file-csv"></i> CSV
                                </a>
                                <a class="btn btn-outline-success btn-sm {{ $isAnalyzed ? '' : 'disabled' }}"
                                   href="{{ $isAnalyzed ? url('/analisis/'.$item->id.'/export/excel') : '#' }}">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <i class="fas fa-folder-open fs-3 text-muted"></i>
                                    <div>Belum ada riwayat analisis.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="pagination-wrap">
                {{ $history->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
</body>
</html>
