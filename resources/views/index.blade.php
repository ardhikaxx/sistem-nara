<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NARA | Naive Bayes Sentiment Analysis</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --primary-light: #eef2ff;
            --secondary: #8b5cf6;
            --success: #10b981;
            --success-light: #d1fae5;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --neutral: #6b7280;
            --neutral-light: #f3f4f6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --border-radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
        }
        
        /* Layout */
        .app-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .app-header {
            background: white;
            border-bottom: 1px solid var(--gray-light);
            padding: 1.25rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }
        
        .brand-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2px;
            line-height: 1.2;
        }
        
        .brand-text p {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .user-profile:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }
        
        /* Card Design */
        .card {
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid var(--gray-light);
            padding: 1.25rem 1.5rem;
        }
        
        .card-header h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* File Upload */
        .upload-area {
            border: 2px dashed var(--gray-light);
            border-radius: var(--border-radius);
            padding: 3rem 2rem;
            text-align: center;
            background: var(--light);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }
        
        .upload-area:hover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.02);
        }
        
        .upload-area.dragover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }
        
        .upload-icon {
            width: 64px;
            height: 64px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: var(--primary);
            font-size: 1.75rem;
        }
        
        .upload-text h4 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .upload-text p {
            color: var(--gray);
            margin-bottom: 1.5rem;
            font-size: 0.9375rem;
        }
        
        .file-info {
            display: none;
            padding: 1rem;
            background: var(--light);
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-light);
            margin-top: 1rem;
        }
        
        .file-info.active {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9375rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #0da271;
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--gray-light);
            color: var(--gray);
        }
        
        .btn-outline:hover {
            background: var(--light);
            border-color: var(--gray);
            color: var(--dark);
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Progress Bar */
        .progress-container {
            display: none;
            margin-top: 1rem;
        }
        
        .progress-container.active {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .progress-bar {
            height: 8px;
            background: var(--gray-light);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 4px;
            transition: width 0.3s ease;
            width: 0%;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.25rem;
            border: 1px solid var(--gray-light);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .positive .stat-icon {
            background: var(--success-light);
            color: var(--success);
        }
        
        .negative .stat-icon {
            background: var(--danger-light);
            color: var(--danger);
        }
        
        .neutral .stat-icon {
            background: var(--warning-light);
            color: var(--warning);
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .positive .stat-value {
            color: var(--success);
        }
        
        .negative .stat-value {
            color: var(--danger);
        }
        
        .neutral .stat-value {
            color: var(--warning);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
        }
        
        /* Accuracy Badge */
        .accuracy-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.75rem 1.25rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9375rem;
        }
        
        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .data-table thead {
            background: var(--light);
        }
        
        .data-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid var(--gray-light);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: middle;
        }
        
        .data-table tbody tr {
            transition: var(--transition);
        }
        
        .data-table tbody tr:hover {
            background: var(--light);
        }
        
        .sentiment-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .sentiment-badge.positive {
            background: var(--success-light);
            color: var(--success);
        }
        
        .sentiment-badge.negative {
            background: var(--danger-light);
            color: var(--danger);
        }
        
        .sentiment-badge.neutral {
            background: var(--warning-light);
            color: var(--warning);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .action-buttons .btn {
            flex: 1;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
        }
        
        /* Sentiment Legend */
        .sentiment-legend {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .legend-color.positive {
            background: var(--success);
        }
        
        .legend-color.negative {
            background: var(--danger);
        }
        
        .legend-color.neutral {
            background: var(--warning);
        }
        
        /* Footer */
        .app-footer {
            background: var(--dark);
            color: white;
            padding: 2rem 0;
            margin-top: auto;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-links {
            display: flex;
            gap: 2rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .brand-text h1 {
                font-size: 1.25rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .footer-links {
                justify-content: center;
            }
            
            .sentiment-legend {
                gap: 1rem;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
        
        /* Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
            background: white;
            border-radius: var(--border-radius);
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-lg);
            border-left: 4px solid var(--primary);
            animation: slideInRight 0.3s ease;
            max-width: 350px;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast.success {
            border-left-color: var(--success);
        }
        
        .toast.error {
            border-left-color: var(--danger);
        }
        
        .toast-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        
        .toast-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .toast.success .toast-icon {
            background: var(--success-light);
            color: var(--success);
        }
        
        .toast.error .toast-icon {
            background: var(--danger-light);
            color: var(--danger);
        }
        
        .toast.info .toast-icon {
            background: var(--primary-light);
            color: var(--primary);
        }
        
        .toast-message {
            flex: 1;
        }
        
        .toast-message h4 {
            font-size: 0.9375rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--dark);
        }
        
        .toast-message p {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="container">
                <div class="header-content">
                    <div class="brand">
                        <div class="brand-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="brand-text">
                            <h1>Sistem NARA</h1>
                            <p>Naive Bayes Analisis Review Aplikasi</p>
                        </div>
                    </div>
                    
                    <div class="header-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-history"></i>
                            <span>Riwayat</span>
                        </button>
                        <button class="btn btn-outline">
                            <i class="fas fa-cog"></i>
                            <span>Pengaturan</span>
                        </button>
                        <div class="user-profile">
                            <i class="fas fa-user"></i>
                            <span>Administrator</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- File Upload Card -->
                        <div class="card fade-in">
                            <div class="card-header">
                                <h3>
                                    <i class="fas fa-upload"></i>
                                    Unggah Data Review
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="upload-area" id="uploadArea">
                                    <div class="upload-icon">
                                        <i class="fas fa-file-csv"></i>
                                    </div>
                                    <div class="upload-text">
                                        <h4>Seret & Jatuhkan file CSV Anda</h4>
                                        <p>Atau klik untuk memilih file dari komputer</p>
                                    </div>
                                    <input type="file" id="fileInput" class="d-none" accept=".csv">
                                    <button class="btn btn-primary" id="browseBtn">
                                        <i class="fas fa-folder-open"></i>
                                        Pilih File CSV
                                    </button>
                                    <p class="mt-3 mb-0 text-muted small">
                                        Format file: CSV dengan kolom "review". Ukuran maksimal: 10MB.
                                    </p>
                                </div>
                                
                                <!-- File Info -->
                                <div class="file-info" id="fileInfo">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="upload-icon" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                                <i class="fas fa-file-csv"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1" id="fileName">nama_file.csv</h6>
                                                <p class="mb-0 text-muted small" id="fileDetails">0 KB • CSV</p>
                                            </div>
                                        </div>
                                        <button class="btn btn-icon btn-outline" id="removeFile">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="progress-container" id="progressContainer">
                                    <div class="progress-label">
                                        <span>Mengimpor data...</span>
                                        <span id="progressText">0%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" id="progressFill"></div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="action-buttons">
                                    <button class="btn btn-primary" id="importBtn" disabled>
                                        <i class="fas fa-database"></i>
                                        Import ke Database
                                    </button>
                                    <button class="btn btn-success" id="analyzeBtn" disabled>
                                        <i class="fas fa-chart-pie"></i>
                                        Analisis Sekarang
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Results Table -->
                        <div class="card fade-in" style="animation-delay: 0.1s;">
                            <div class="card-header">
                                <h3>
                                    <i class="fas fa-table"></i>
                                    Data Review
                                    <span class="badge bg-primary ms-2" id="totalReviews">0 review</span>
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Ulasan</th>
                                                <th style="width: 120px;">Sentimen</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reviewTable">
                                            <tr>
                                                <td colspan="3">
                                                    <div class="empty-state">
                                                        <i class="fas fa-file-import"></i>
                                                        <h4 class="mt-3 mb-2">Belum ada data</h4>
                                                        <p class="mb-0">Unggah file CSV untuk memulai analisis</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Results Card -->
                        <div class="card fade-in" style="animation-delay: 0.2s;">
                            <div class="card-header">
                                <h3>
                                    <i class="fas fa-chart-bar"></i>
                                    Hasil Analisis Sentimen
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Initial State -->
                                <div id="initialState">
                                    <div class="empty-state" style="padding: 2rem 1rem;">
                                        <i class="fas fa-chart-line"></i>
                                        <h4 class="mt-3 mb-2">Analisis Siap</h4>
                                        <p class="mb-3">Import data dan klik "Analisis Sekarang" untuk melihat hasil analisis sentimen menggunakan Naive Bayes</p>
                                        <div class="accuracy-badge">
                                            <i class="fas fa-bullseye"></i>
                                            Naive Bayes Algorithm
                                        </div>
                                        <div class="mt-3">
                                            <p class="small text-muted mb-2">Klasifikasi Sentimen:</p>
                                            <div class="sentiment-legend">
                                                <div class="legend-item">
                                                    <div class="legend-color positive"></div>
                                                    <span>Positif</span>
                                                </div>
                                                <div class="legend-item">
                                                    <div class="legend-color negative"></div>
                                                    <span>Negatif</span>
                                                </div>
                                                <div class="legend-item">
                                                    <div class="legend-color neutral"></div>
                                                    <span>Netral</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Results State -->
                                <div id="resultsState" style="display: none;">
                                    <!-- Stats Grid -->
                                    <div class="stats-grid">
                                        <div class="stat-card positive">
                                            <div class="stat-icon">
                                                <i class="fas fa-smile"></i>
                                            </div>
                                            <div class="stat-value" id="positiveCount">0</div>
                                            <div class="stat-label">Review Positif</div>
                                            <div class="mt-2 small text-muted" id="positivePercent">0%</div>
                                        </div>
                                        
                                        <div class="stat-card negative">
                                            <div class="stat-icon">
                                                <i class="fas fa-frown"></i>
                                            </div>
                                            <div class="stat-value" id="negativeCount">0</div>
                                            <div class="stat-label">Review Negatif</div>
                                            <div class="mt-2 small text-muted" id="negativePercent">0%</div>
                                        </div>
                                        
                                        <div class="stat-card neutral">
                                            <div class="stat-icon">
                                                <i class="fas fa-meh"></i>
                                            </div>
                                            <div class="stat-value" id="neutralCount">0</div>
                                            <div class="stat-label">Review Netral</div>
                                            <div class="mt-2 small text-muted" id="neutralPercent">0%</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Chart -->
                                    <div class="chart-container">
                                        <canvas id="sentimentChart"></canvas>
                                    </div>
                                    
                                    <!-- Legend -->
                                    <div class="sentiment-legend">
                                        <div class="legend-item">
                                            <div class="legend-color positive"></div>
                                            <span>Positif</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color negative"></div>
                                            <span>Negatif</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-color neutral"></div>
                                            <span>Netral</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Accuracy -->
                                    <div class="text-center my-4">
                                        <p class="mb-2 text-muted">Akurasi Model Naive Bayes</p>
                                        <div class="accuracy-badge">
                                            <i class="fas fa-bullseye"></i>
                                            <span id="accuracyValue">0%</span>
                                        </div>
                                        <p class="mt-2 small text-muted">
                                            Tingkat akurasi klasifikasi sentimen
                                        </p>
                                    </div>
                                    
                                    <!-- Summary -->
                                    <div class="border-top pt-3">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="h5 mb-1" id="totalCount">0</div>
                                                <div class="small text-muted">Total Review</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="h5 mb-1 text-success" id="confidenceScore">0%</div>
                                                <div class="small text-muted">Kepercayaan</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="h5 mb-1 text-primary" id="processingTime">0s</div>
                                                <div class="small text-muted">Waktu Proses</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Card -->
                        <div class="card fade-in" style="animation-delay: 0.3s;">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    <i class="fas fa-info-circle text-primary"></i>
                                    Klasifikasi Sentimen
                                </h5>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="sentiment-badge positive me-2">Positif</div>
                                        <span class="small text-muted">Review dengan kata-kata positif dan puas</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="sentiment-badge negative me-2">Negatif</div>
                                        <span class="small text-muted">Review dengan kritik dan ketidakpuasan</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="sentiment-badge neutral me-2">Netral</div>
                                        <span class="small text-muted">Review dengan informasi faktual tanpa emosi</span>
                                    </div>
                                </div>
                                <div class="border-top pt-3">
                                    <h6 class="mb-2">Cara Kerja Sistem</h6>
                                    <div class="d-flex mb-2">
                                        <div class="me-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">
                                                1
                                            </div>
                                        </div>
                                        <div>
                                            <p class="mb-0 small text-muted">Upload file CSV berisi review aplikasi</p>
                                        </div>
                                    </div>
                                    <div class="d-flex mb-2">
                                        <div class="me-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">
                                                2
                                            </div>
                                        </div>
                                        <div>
                                            <p class="mb-0 small text-muted">Data diproses dengan algoritma Naive Bayes</p>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="me-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">
                                                3
                                            </div>
                                        </div>
                                        <div>
                                            <p class="mb-0 small text-muted">Hasil klasifikasi sentimen ditampilkan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="app-footer">
            <div class="container">
                <div class="footer-content">
                    <div>
                        <p class="mb-0">&copy; 2023 Sistem NARA. All rights reserved.</p>
                    </div>
                    <div class="footer-links">
                        <a href="#"><i class="fas fa-question-circle me-1"></i> Bantuan</a>
                        <a href="#"><i class="fas fa-file-alt me-1"></i> Dokumentasi</a>
                        <a href="#"><i class="fas fa-envelope me-1"></i> Kontak</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const fileInput = document.getElementById('fileInput');
            const browseBtn = document.getElementById('browseBtn');
            const uploadArea = document.getElementById('uploadArea');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileDetails = document.getElementById('fileDetails');
            const removeFileBtn = document.getElementById('removeFile');
            const importBtn = document.getElementById('importBtn');
            const analyzeBtn = document.getElementById('analyzeBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const reviewTable = document.getElementById('reviewTable');
            const totalReviews = document.getElementById('totalReviews');
            const initialState = document.getElementById('initialState');
            const resultsState = document.getElementById('resultsState');
            const positiveCount = document.getElementById('positiveCount');
            const negativeCount = document.getElementById('negativeCount');
            const neutralCount = document.getElementById('neutralCount');
            const positivePercent = document.getElementById('positivePercent');
            const negativePercent = document.getElementById('negativePercent');
            const neutralPercent = document.getElementById('neutralPercent');
            const accuracyValue = document.getElementById('accuracyValue');
            const totalCount = document.getElementById('totalCount');
            const confidenceScore = document.getElementById('confidenceScore');
            const processingTime = document.getElementById('processingTime');
            const toastContainer = document.getElementById('toastContainer');
            
            let currentFile = null;
            let sentimentChart = null;
            
            // Event Listeners
            browseBtn.addEventListener('click', () => fileInput.click());
            uploadArea.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', handleFileSelect);
            removeFileBtn.addEventListener('click', removeFile);
            importBtn.addEventListener('click', importFile);
            analyzeBtn.addEventListener('click', analyzeData);
            
            // Drag and Drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                uploadArea.classList.add('dragover');
            }
            
            function unhighlight() {
                uploadArea.classList.remove('dragover');
            }
            
            uploadArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length > 0) {
                    const file = files[0];
                    if (file.name.toLowerCase().endsWith('.csv')) {
                        handleFile(file);
                    } else {
                        showToast('Hanya file CSV yang diizinkan', 'error');
                    }
                }
            }
            
            function handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    handleFile(file);
                }
            }
            
            function handleFile(file) {
                if (!file.name.toLowerCase().endsWith('.csv')) {
                    showToast('Silakan pilih file CSV yang valid', 'error');
                    return;
                }
                
                currentFile = file;
                const fileSize = formatFileSize(file.size);
                
                // Update file info
                fileName.textContent = file.name;
                fileDetails.textContent = `${fileSize} • CSV`;
                fileInfo.classList.add('active');
                
                // Enable import button
                importBtn.disabled = false;
                
                // Preview file content
                previewFile(file);
                
                showToast(`File "${file.name}" berhasil dipilih`, 'success');
            }
            
            function removeFile() {
                fileInput.value = '';
                currentFile = null;
                fileInfo.classList.remove('active');
                importBtn.disabled = true;
                analyzeBtn.disabled = true;
                reviewTable.innerHTML = `
                    <tr>
                        <td colspan="3">
                            <div class="empty-state">
                                <i class="fas fa-file-import"></i>
                                <h4 class="mt-3 mb-2">Belum ada data</h4>
                                <p class="mb-0">Unggah file CSV untuk memulai analisis</p>
                            </div>
                        </td>
                    </tr>
                `;
                totalReviews.textContent = '0 review';
                
                // Reset results
                initialState.style.display = 'block';
                resultsState.style.display = 'none';
                
                showToast('File berhasil dihapus', 'info');
            }
            
            function importFile() {
                if (!currentFile) return;
                
                // Show progress
                progressContainer.classList.add('active');
                importBtn.disabled = true;
                importBtn.innerHTML = '<div class="spinner"></div> Mengimpor...';
                
                // Simulate import process
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 20;
                    if (progress > 95) progress = 95;
                    
                    progressFill.style.width = `${progress}%`;
                    progressText.textContent = `${Math.round(progress)}%`;
                }, 300);
                
                // Simulate completion
                setTimeout(() => {
                    clearInterval(interval);
                    progressFill.style.width = '100%';
                    progressText.textContent = '100%';
                    
                    setTimeout(() => {
                        // Reset progress
                        progressContainer.classList.remove('active');
                        progressFill.style.width = '0%';
                        progressText.textContent = '0%';
                        
                        // Update UI
                        importBtn.style.display = 'none';
                        analyzeBtn.disabled = false;
                        
                        // Update file info style
                        fileInfo.classList.add('imported');
                        
                        showToast('Data berhasil diimport ke database', 'success');
                    }, 500);
                }, 2000);
            }
            
            function analyzeData() {
                analyzeBtn.disabled = true;
                analyzeBtn.innerHTML = '<div class="spinner"></div> Menganalisis...';
                
                // Generate sample results with neutral sentiment
                const positive = Math.floor(Math.random() * 120) + 30;
                const negative = Math.floor(Math.random() * 80) + 15;
                const neutral = Math.floor(Math.random() * 60) + 10;
                const total = positive + negative + neutral;
                const positivePct = Math.round((positive / total) * 100);
                const negativePct = Math.round((negative / total) * 100);
                const neutralPct = Math.round((neutral / total) * 100);
                const accuracy = Math.floor(Math.random() * 15) + 85;
                const confidence = Math.floor(Math.random() * 10) + 90;
                const processTime = (Math.random() * 1.5 + 0.5).toFixed(1);
                
                setTimeout(() => {
                    // Update results
                    positiveCount.textContent = positive;
                    negativeCount.textContent = negative;
                    neutralCount.textContent = neutral;
                    positivePercent.textContent = `${positivePct}% dari total`;
                    negativePercent.textContent = `${negativePct}% dari total`;
                    neutralPercent.textContent = `${neutralPct}% dari total`;
                    accuracyValue.textContent = `${accuracy}%`;
                    totalCount.textContent = total;
                    confidenceScore.textContent = `${confidence}%`;
                    processingTime.textContent = `${processTime}s`;
                    totalReviews.textContent = `${total} review`;
                    
                    // Show results
                    initialState.style.display = 'none';
                    resultsState.style.display = 'block';
                    
                    // Update chart
                    updateChart(positive, negative, neutral);
                    
                    // Update table with sample data including neutral
                    updateTable(total, positive, negative, neutral);
                    
                    // Reset button
                    analyzeBtn.disabled = false;
                    analyzeBtn.innerHTML = '<i class="fas fa-redo"></i> Analisis Ulang';
                    
                    showToast(`Analisis selesai! ${positive} positif, ${negative} negatif, ${neutral} netral`, 'success');
                }, 1500);
            }
            
            function previewFile(file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const content = e.target.result;
                    const lines = content.split('\n').slice(0, 6);
                    
                    if (lines.length <= 1) {
                        reviewTable.innerHTML = `
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <h4 class="mt-3 mb-2">File kosong</h4>
                                        <p class="mb-0">File tidak berisi data yang valid</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    let tableHTML = '';
                    const headers = lines[0].split(',');
                    const reviewIndex = headers.findIndex(h => h.toLowerCase().includes('review'));
                    
                    if (reviewIndex === -1) {
                        reviewTable.innerHTML = `
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <h4 class="mt-3 mb-2">Format tidak valid</h4>
                                        <p class="mb-0">Kolom "review" tidak ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    for (let i = 1; i < Math.min(lines.length, 6); i++) {
                        if (lines[i].trim()) {
                            const columns = lines[i].split(',');
                            const review = columns[reviewIndex]?.trim() || '';
                            if (review) {
                                tableHTML += `
                                    <tr>
                                        <td>${i}</td>
                                        <td>${review.length > 60 ? review.substring(0, 60) + '...' : review}</td>
                                        <td><span class="sentiment-badge neutral">Belum dianalisis</span></td>
                                    </tr>
                                `;
                            }
                        }
                    }
                    
                    reviewTable.innerHTML = tableHTML || `
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h4 class="mt-3 mb-2">Tidak ada data</h4>
                                    <p class="mb-0">File tidak berisi review yang valid</p>
                                </td>
                            </tr>
                        </tr>
                    `;
                };
                
                reader.readAsText(file);
            }
            
            function updateTable(total, positive, negative, neutral) {
                // Create sample reviews with all three sentiments
                const sampleReviews = [
                    { text: "Aplikasi sangat membantu dalam pekerjaan sehari-hari", sentiment: "positive" },
                    { text: "UI/UX yang menarik dan mudah digunakan", sentiment: "positive" },
                    { text: "Sering error ketika membuka file besar", sentiment: "negative" },
                    { text: "Update terbaru membuat aplikasi lebih lambat", sentiment: "negative" },
                    { text: "Aplikasi ini tersedia di Play Store dan App Store", sentiment: "neutral" },
                    { text: "Fitur lengkap dan customer service responsif", sentiment: "positive" },
                    { text: "Membutuhkan koneksi internet yang stabil", sentiment: "neutral" },
                    { text: "Konsumsi baterai terlalu tinggi", sentiment: "negative" },
                    { text: "Tersedia dalam beberapa bahasa", sentiment: "neutral" },
                    { text: "Proses login yang cepat dan aman", sentiment: "positive" }
                ];
                
                // Calculate distribution for preview
                const totalSample = Math.min(8, total);
                let tableHTML = '';
                
                // Show proportional representation
                let positiveShown = 0, negativeShown = 0, neutralShown = 0;
                const positiveTarget = Math.round((positive / total) * totalSample);
                const negativeTarget = Math.round((negative / total) * totalSample);
                const neutralTarget = totalSample - positiveTarget - negativeTarget;
                
                for (let i = 0; i < sampleReviews.length && (positiveShown + negativeShown + neutralShown) < totalSample; i++) {
                    const review = sampleReviews[i];
                    
                    // Check if we need more of this sentiment
                    if (review.sentiment === 'positive' && positiveShown < positiveTarget) {
                        tableHTML += createTableRow(positiveShown + negativeShown + neutralShown + 1, review.text, review.sentiment);
                        positiveShown++;
                    } else if (review.sentiment === 'negative' && negativeShown < negativeTarget) {
                        tableHTML += createTableRow(positiveShown + negativeShown + neutralShown + 1, review.text, review.sentiment);
                        negativeShown++;
                    } else if (review.sentiment === 'neutral' && neutralShown < neutralTarget) {
                        tableHTML += createTableRow(positiveShown + negativeShown + neutralShown + 1, review.text, review.sentiment);
                        neutralShown++;
                    }
                }
                
                if (total > totalSample) {
                    tableHTML += `
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                <i class="fas fa-ellipsis-h me-1"></i>
                                Dan ${total - totalSample} review lainnya
                            </td>
                        </tr>
                    `;
                }
                
                reviewTable.innerHTML = tableHTML;
            }
            
            function createTableRow(index, text, sentiment) {
                const sentimentText = sentiment === 'positive' ? 'Positif' : 
                                     sentiment === 'negative' ? 'Negatif' : 'Netral';
                
                return `
                    <tr>
                        <td>${index}</td>
                        <td>${text}</td>
                        <td><span class="sentiment-badge ${sentiment}">${sentimentText}</span></td>
                    </tr>
                `;
            }
            
            function updateChart(positive, negative, neutral) {
                const ctx = document.getElementById('sentimentChart').getContext('2d');
                
                if (sentimentChart) {
                    sentimentChart.destroy();
                }
                
                sentimentChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Positif', 'Negatif', 'Netral'],
                        datasets: [{
                            data: [positive, negative, neutral],
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(245, 158, 11, 0.8)'
                            ],
                            borderColor: [
                                'rgba(16, 185, 129, 1)',
                                'rgba(239, 68, 68, 1)',
                                'rgba(245, 158, 11, 1)'
                            ],
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 13
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
            
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                
                const icon = type === 'success' ? 'fa-check-circle' : 
                            type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
                
                toast.innerHTML = `
                    <div class="toast-content">
                        <div class="toast-icon">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="toast-message">
                            <h4>${type === 'success' ? 'Berhasil' : type === 'error' ? 'Error' : 'Info'}</h4>
                            <p>${message}</p>
                        </div>
                        <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                toastContainer.appendChild(toast);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 5000);
            }
            
            // Initialize chart with three categories
            const ctx = document.getElementById('sentimentChart').getContext('2d');
            sentimentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Positif', 'Negatif', 'Netral'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.1)',
                            'rgba(239, 68, 68, 0.1)',
                            'rgba(245, 158, 11, 0.1)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 0.3)',
                            'rgba(239, 68, 68, 0.3)',
                            'rgba(245, 158, 11, 0.3)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '65%'
                }
            });
        });
    </script>
</body>
</html>