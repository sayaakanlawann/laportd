<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Logbook TD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f172a; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .login-card { background-color: #1e293b; border: 1px solid #334155; border-radius: 16px; width: 100%; max-width: 400px; padding: 2.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3); }
        .form-control { background-color: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 0.75rem 1rem; border-radius: 8px; }
        .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25); background-color: #0f172a; color: white;}
        .btn-primary { background-color: #6366f1; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; width: 100%; }
        .btn-primary:hover { background-color: #4f46e5; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="text-white fw-bold mb-1">Otentikasi Kru</h3>
            <p class="text-muted small">Silakan masuk untuk melanjutkan</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger small p-2 mb-3 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Email Petugas</label>
                <input type="email" name="email" class="form-control" placeholder="namadepan@tvrikalsel.id" required autofocus>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-muted small fw-bold">Kata Sandi</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary mb-3">🚀 Masuk Sistem</button>
            <a href="/" class="btn btn-outline-secondary w-100" style="border-radius: 8px;">🏠 Kembali ke Lobi</a>
        </form>
    </div>

</body>
</html>