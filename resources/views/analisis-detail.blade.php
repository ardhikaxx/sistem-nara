<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Analisis - {{ $analisis->nama_analisis }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --primary: #1d4ed8;
            --primary-2: #60a5fa;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #d97706;
            --bg: #f5f7fb;
            --card: #ffffff;
            --border: #e2e8f0;
            --shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            --radius: 16px;
        }

        body {
            background: radial-gradient(circle at 10% 0%, #eef2ff 0%, #f8fafc 40%, #f1f5f9 100%);
            color: var(--ink);
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3, h4 {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .page-hero {
            background: linear-gradient(135deg, #1d4ed8, #2563eb 45%, #60a5fa);
            color: #fff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--shadow);
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 600;
        }

        .card {
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .stat {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }

        .stat::after {
            content: '';
            position: absolute;
            inset: auto -40% -60% auto;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            opacity: 0.15;
            background: currentColor;
        }

        .stat h3 {
            font-size: 1.6rem;
            margin: 0;
            font-weight: 800;
        }

        .stat small {
            color: var(--muted);
            font-weight: 600;
        }

        .stat.positive { color: var(--success); }
        .stat.negative { color: var(--danger); }
        .stat.neutral { color: var(--warning); }

        .badge-soft { padding: 6px 10px; border-radius: 999px; font-weight: 700; font-size: 0.8rem; }
        .badge-positive { background: #dcfce7; color: #15803d; }
        .badge-negative { background: #fee2e2; color: #b91c1c; }
        .badge-neutral { background: #fef3c7; color: #b45309; }
        .rating-stars {
            color: #fbbf24;
            display: inline-flex;
            gap: 2px;
            font-size: 0.95rem;
        }
        .rating-stars .star-muted { color: #e5e7eb; }

        .table thead th {
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        .table-hover tbody tr:hover {
            background: #f8fafc;
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

        .chart-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px;
            box-shadow: var(--shadow);
        }
        .chart-wrap { height: 260px; }

        .pagination-wrap { display: flex; justify-content: center; }
        .pagination { gap: 6px; }
        .page-link {
            border-radius: 999px !important;
            border: 1px solid var(--border);
            color: #334155;
            padding: 8px 14px;
            box-shadow: none;
            transition: all 0.15s ease;
        }
        .page-link:hover {
            background: #eef2ff;
            color: var(--primary);
            border-color: #c7d2fe;
        }
        .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 6px 14px rgba(29, 78, 216, 0.25);
            transform: translateY(-1px);
        }
        .page-item.disabled .page-link {
            background: #f8fafc;
            color: #94a3b8;
            border-color: var(--border);
        }
        .pagination-meta {
            color: var(--muted);
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
    <div class="page-hero mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
            <h2 class="mb-2">Detail Analisis {{ $analisis->nama_analisis }}</h2>
            <div class="hero-meta">
                <span class="hero-badge">Tanggal: {{ \Carbon\Carbon::parse($analisis->tanggal_analisis)->locale('id')->translatedFormat('d F Y') }}</span>
                    <span class="hero-badge">Total Review: {{ $totalReviews }}</span>
                    <span class="hero-badge">Status: {{ $isAnalyzed ? 'Selesai' : 'Belum Analisis' }}</span>
                </div>
            </div>
            <div class="d-flex gap-2 pill-actions">
                <a class="btn btn-ghost" href="{{ url('/') }}"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a class="btn btn-outline-light {{ $isAnalyzed ? '' : 'disabled' }}" href="{{ $isAnalyzed ? url('/analisis/'.$analisis->id.'/export/csv') : '#' }}">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a class="btn btn-outline-light {{ $isAnalyzed ? '' : 'disabled' }}" href="{{ $isAnalyzed ? url('/analisis/'.$analisis->id.'/export/excel') : '#' }}">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </div>
    </div>

    @if(!$isAnalyzed)
        <div class="alert alert-warning">Analisis belum dijalankan. Silakan jalankan analisis terlebih dahulu.</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat">
                <small>Total Review</small>
                <h3>{{ $totalReviews }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat positive">
                <small>Positif</small>
                <h3>{{ $positive }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat neutral">
                <small>Netral</small>
                <h3>{{ $neutral }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat negative">
                <small>Negatif</small>
                <h3>{{ $negative }}</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat">
                <small>Total Dianalisis</small>
                <h3>{{ $totalAnalyzed }}</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat">
                <small>Rata-rata Confidence</small>
                <h3>{{ $averageConfidence ? number_format($averageConfidence * 100, 2) : '0.00' }}%</h3>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="chart-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <strong>Distribusi Sentimen</strong>
                    <span class="badge-soft badge-neutral">Total {{ $totalAnalyzed }}</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="sentimentChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <strong>Data Ulasan</strong>
            <span class="badge-soft badge-neutral">Halaman {{ $reviews->currentPage() }} dari {{ $reviews->lastPage() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
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
                            <td>
                                @if($review->rating)
                                    @php $rating = (int) $review->rating; @endphp
                                    <span class="rating-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $rating ? '' : 'star-muted' }}"></i>
                                        @endfor
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge-soft {{ $badgeClass }}">
                                    {{ $sentiment === 'positive' ? 'Positif' : ($sentiment === 'negative' ? 'Negatif' : 'Netral') }}
                                </span>
                            </td>
                            <td>{{ $review->confidence !== null ? number_format($review->confidence * 100, 2) . '%' : '-' }}</td>
                            <td>{{ $review->review_date ? $review->review_date->locale('id')->translatedFormat('d M Y') : '-' }}</td>
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
<script>
    const chartCtx = document.getElementById('sentimentChart');
    if (chartCtx) {
        new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: ['Positif', 'Negatif', 'Netral'],
                datasets: [{
                    data: [{{ $positive }}, {{ $negative }}, {{ $neutral }}],
                    backgroundColor: [
                        'rgba(22, 163, 74, 0.9)',
                        'rgba(220, 38, 38, 0.9)',
                        'rgba(217, 119, 6, 0.9)'
                    ],
                    borderWidth: 0,
                    borderRadius: 10,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 900,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw || 0;
                                const total = {{ $totalAnalyzed }};
                                const pct = total ? Math.round((value / total) * 100) : 0;
                                return `${value} (${pct}%)`;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { color: '#eef2f7' }, ticks: { precision: 0 } },
                    y: { grid: { display: false } }
                },
                indexAxis: 'y'
            },
            plugins: [{
                id: 'valueLabels',
                afterDatasetsDraw(chart) {
                    const { ctx } = chart;
                    const meta = chart.getDatasetMeta(0);
                    ctx.save();
                    ctx.fillStyle = '#0f172a';
                    ctx.font = '600 12px Inter, sans-serif';
                    meta.data.forEach((bar, index) => {
                        const value = chart.data.datasets[0].data[index] || 0;
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(value, bar.x + 6, bar.y);
                    });
                    ctx.restore();
                }
            }]
        });
    }
</script>
</body>
</html>
