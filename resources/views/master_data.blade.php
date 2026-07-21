<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Master Data - TVRI Kalsel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { background-color: #0f172a; color: #cbd5e1; }
        .card { background-color: #1e293b; border-color: #334155; }
        .editable { cursor: text; border: 1px dashed transparent; transition: all 0.2s; }
        .editable:hover { border-color: #6366f1; background-color: #334155; }
        .editable:focus { outline: 2px solid #6366f1; background-color: #0f172a; }
    </style>
</head>
<body class="p-5">

    <div class="container-fluid" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-white">⚙️ Kelola Master Data</h3>
            <a href="/" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
        </div>

        <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 text-info mb-4">
            💡 <strong>Tips:</strong> Klik langsung pada teks di dalam tabel untuk mengeditnya. Data otomatis tersimpan saat Anda memindahkan kursor (klik di luar).
        </div>
        <!-- Pesan Sukses Tambah Data -->
        @if(session('success'))
            <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-success d-flex align-items-center mb-4" role="alert">
                <span class="me-2">✓</span> {{ session('success') }}
            </div>
        @endif
        <!-- ===================================== -->
        <!-- TABEL PETUGAS -->
        <!-- ===================================== -->
        <h5 class="text-white mb-3">Daftar Kru / Petugas</h5>
        <div class="card shadow mb-5">
            <div class="card-body p-0">
                <table class="table table-dark table-hover mb-0">
                    <thead class="table-active">
                        <tr>
                            <th width="10%" class="text-center">No</th>
                            <th width="40%">Nama Petugas</th>
                            <th width="40%">Jabatan Utama</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($petugas as $index => $p)
                        <tr>
                            <td class="text-center align-middle">{{ $index + 1 }}</td>
                            <!-- Perhatikan tambahan data-endpoint -->
                            <td contenteditable="true" class="editable p-3" 
                                data-id="{{ $p->id }}" data-column="nama" data-endpoint="/master-data/update-petugas">{{ $p->nama }}</td>
                            <td contenteditable="true" class="editable p-3" 
                                data-id="{{ $p->id }}" data-column="jabatan_utama" data-endpoint="/master-data/update-petugas">{{ $p->jabatan_utama }}</td>
                            <td class="text-center align-middle">
                                <form action="/master-data/delete-petugas/{{ $p->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus petugas ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- FORM TAMBAH PETUGAS -->
            <div class="card-footer border-secondary bg-dark p-3">
                <form action="/master-data/store-petugas" method="POST" class="row g-2 align-items-center">
                    @csrf
                    <div class="col-auto"><span class="badge bg-primary bg-opacity-25 text-primary border border-primary p-2">➕ Tambah Baru</span></div>
                    <div class="col-md-4">
                        <input type="text" name="nama" class="form-control form-control-sm bg-secondary bg-opacity-10 text-white border-secondary" placeholder="Nama Petugas (cth: Fulan)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="jabatan_utama" class="form-control form-control-sm bg-secondary bg-opacity-10 text-white border-secondary" placeholder="Jabatan (cth: Pengendali Siaran)" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-success">Simpan</button>
                    </div>
                </form>
            </div>
            
        </div>

        <!-- ===================================== -->
        <!-- TABEL PROGRAM SIARAN -->
        <!-- ===================================== -->
        <h5 class="text-white mb-3">Daftar Program Siaran</h5>
        <div class="card shadow mb-4">
            <div class="card-body p-0">
                <table class="table table-dark table-hover mb-0">
                    <thead class="table-active">
                        <tr>
                            <th width="10%" class="text-center">No</th>
                            <th width="50%">Nama Program</th>
                            <th width="30%">Jam Tayang Default</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programs as $index => $prog)
                        <tr>
                            <td class="text-center align-middle">{{ $index + 1 }}</td>
                            <!-- data-endpoint mengarah ke update-program -->
                            <td contenteditable="true" class="editable p-3" 
                                data-id="{{ $prog->id }}" data-column="nama_program" data-endpoint="/master-data/update-program">{{ $prog->nama_program }}</td>
                            <td contenteditable="true" class="editable p-3" 
                                data-id="{{ $prog->id }}" data-column="jam_tayang_default" data-endpoint="/master-data/update-program">{{ $prog->jam_tayang_default }}</td>
                            <td class="text-center align-middle">
                                <form action="/master-data/delete-program/{{ $prog->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- FORM TAMBAH PROGRAM -->
            <div class="card-footer border-secondary bg-dark p-3">
                <form action="/master-data/store-program" method="POST" class="row g-2 align-items-center">
                    @csrf
                    <div class="col-auto"><span class="badge bg-primary bg-opacity-25 text-primary border border-primary p-2">➕ Tambah Baru</span></div>
                    <div class="col-md-4">
                        <input type="text" name="nama_program" class="form-control form-control-sm bg-secondary bg-opacity-10 text-white border-secondary" placeholder="Nama Acara (cth: Bincang Banua)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="time" name="jam_tayang_default" class="form-control form-control-sm bg-secondary bg-opacity-10 text-white border-secondary" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Notifikasi Toast -->
        <div id="toast-notif" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11; display: none;">
            <div class="toast show align-items-center text-bg-success border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">✅ Perubahan berhasil disimpan!</div>
                </div>
            </div>
        </div>

    </div>

    

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const toast = document.getElementById('toast-notif');

        document.querySelectorAll('.editable').forEach(cell => {
            cell.addEventListener('blur', function() {
                let id = this.getAttribute('data-id');
                let column = this.getAttribute('data-column');
                let endpoint = this.getAttribute('data-endpoint'); // Ambil URL spesifik dari tag HTML
                let newValue = this.innerText.trim();

                fetch(endpoint, { // Gunakan URL dinamis
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ id: id, column: column, value: newValue })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        toast.style.display = 'block';
                        setTimeout(() => toast.style.display = 'none', 2000);
                        
                        this.style.backgroundColor = '#10b981';
                        setTimeout(() => this.style.backgroundColor = '', 500);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
            
            cell.addEventListener('keypress', function(e) {
                if(e.key === 'Enter') {
                    e.preventDefault();
                    this.blur();
                }
            });
        });
    </script>
</body>
</html>