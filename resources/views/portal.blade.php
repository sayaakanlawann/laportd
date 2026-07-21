<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Laporan TD - TVRI Kalsel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #0f172a; 
            font-family: 'Inter', sans-serif; 
            color: #cbd5e1; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0; 
        }
        .portal-container { max-width: 900px; width: 100%; padding: 2rem; }
        .portal-title { color: #f8fafc; font-weight: 700; font-size: 2.25rem; margin-bottom: 0.5rem; text-align: center; }
        .portal-subtitle { color: #94a3b8; text-align: center; margin-bottom: 4rem; font-size: 1.1rem; }
        
        .shift-card { 
            background-color: #1e293b; border: 1px solid #334155; border-radius: 20px; 
            padding: 3rem 2rem; text-align: center; transition: all 0.3s ease; 
            text-decoration: none; display: block; cursor: pointer; 
        }
        .shift-icon { font-size: 4.5rem; margin-bottom: 1.5rem; }
        .shift-title { color: #f8fafc; font-weight: 700; font-size: 1.75rem; margin-bottom: 0.75rem; }
        
        .shift-card.morning:hover { transform: translateY(-8px); border-color: #f59e0b; }
        .shift-time-morning { color: #fbbf24; background: rgba(245, 158, 11, 0.1); padding: 0.5rem 1rem; border-radius: 999px; font-weight: 600; border: 1px solid rgba(245, 158, 11, 0.2); }

        .shift-card.evening:hover { transform: translateY(-8px); border-color: #6366f1; }
        .shift-time-evening { color: #818cf8; background: rgba(99, 102, 241, 0.1); padding: 0.5rem 1rem; border-radius: 999px; font-weight: 600; border: 1px solid rgba(99, 102, 241, 0.2); }

        .btn-outline-custom { border: 1px solid #334155; color: #cbd5e1; border-radius: 10px; padding: 0.75rem 1.5rem; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-outline-custom:hover { background-color: #1e293b; color: #f8fafc; }
    </style>
</head>
<body>
    <!-- Badge Profil Pojok Kanan Atas -->
    <div class="position-absolute top-0 end-0 p-4">
        @auth
            <div class="d-flex align-items-center gap-3 bg-secondary bg-opacity-25 px-3 py-2 rounded-pill border border-secondary border-opacity-50 shadow-sm">
                <div class="text-end">
                    <div class="text-white fw-bold" style="font-size: 0.85rem;">{{ auth()->user()->name }}</div>
                    <div class="text-info" style="font-size: 0.7rem;">{{ strtoupper(auth()->user()->role) }} E-Logbook</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill fw-bold" style="font-size: 0.75rem;">Logout</button>
                </form>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn btn-outline-info rounded-pill fw-bold px-4 shadow-sm">🔑 Login Petugas</a>
        @endauth
    </div>
    <div class="portal-container">
        <h1 class="portal-title">E-Logbook TD</h1>
        <p class="portal-subtitle">Pilih jadwal shift siaran Anda hari ini</p>

        <div class="row g-4 justify-content-center mb-5">
            <!-- Menuju rute /upload yang sudah ada di web.php Abang, ditambah parameter shift -->
            <div class="col-md-6">
                <a href="/upload?shift=pagi" class="shift-card morning">
                    <div class="shift-icon">☀️</div>
                    <h2 class="shift-title">Shift Pagi</h2>
                    <div class="shift-time-morning">09.00 - 14:00 WITA</div>
                </a>
            </div>

            <div class="col-md-6">
                <a href="/upload?shift=sore" class="shift-card evening">
                    <div class="shift-icon">🌙</div>
                    <h2 class="shift-title">Shift Sore</h2>
                    <div class="shift-time-evening">15:00 - 18.00 WITA</div>
                </a>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3">
            @if(auth()->check() && auth()->user()->role === 'admin')
                <a href="/evidence" class="btn-outline-custom">📄 Riwayat Laporan</a>
                <a href="/master-data" class="btn-outline-custom">⚙️ Master Data</a>
            @endif
        </div>
    </div>

</body>
</html>