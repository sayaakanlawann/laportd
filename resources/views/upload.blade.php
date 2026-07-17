<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan TD Sore - TVRI Kalsel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f172a; font-family: 'Inter', sans-serif; color: #cbd5e1; }
        .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); }
        .card-header { border-bottom: 1px solid #334155; background: transparent; padding: 1.5rem; }
        .card-body { padding: 2rem; }
        .form-label { color: #f8fafc; font-weight: 500; font-size: 0.875rem; }
        .form-control, .form-select { background-color: #0f172a; border: 1px solid #334155; color: #f8fafc; border-radius: 8px; padding: 0.75rem 1rem; }
        .form-control:focus, .form-select:focus { background-color: #0f172a; border-color: #6366f1; box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25); color: #f8fafc; }
        .btn-primary { background-color: #6366f1; border: none; color: white; border-radius: 8px; font-weight: 600; padding: 0.75rem 1.5rem; }
        .btn-primary:hover { background-color: #4f46e5; }
        .section-title { color: #818cf8; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid #334155; padding-bottom: 0.5rem; }
    </style>
</head>
<body>

    <div class="container mt-5 mb-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white fw-bold">Input Laporan Induk TD Sore</h5>
            </div>
            <div class="card-body">
                <form action="/upload" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="section-title">Data Personil & Waktu</div>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Tugas</label>
                            <input type="date" name="tanggal_tugas" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama TD (Technical Director)</label>
                            <input type="text" name="nama_petugas" class="form-control" placeholder="Masukkan nama..." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Petugas PDU</label>
                            <input type="text" name="pdu_nama" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Petugas Transmisi</label>
                            <input type="text" name="tx_petugas_nama" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Kehadiran Kru</label>
                            <select name="kru_lengkap" class="form-select" required>
                                <option value="1">Lengkap</option>
                                <option value="0">Tidak Lengkap</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Evidence Rutin (Pra-Siaran)</div>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Alat Studio & Master</label>
                            <input type="file" name="ev_alat_studio" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pengecekan Jaringan</label>
                            <input type="file" name="ev_jaringan" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jalur Audio & Video</label>
                            <input type="file" name="ev_jalur_av" class="form-control" required>
                        </div>
                    </div>

                    <div class="section-title">Status Kendala Pra-Siaran</div>
                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Apakah ada kendala sebelum siaran?</label>
                            <select name="pra_kendala" id="pra_kendala_select" class="form-select" required onchange="toggleKendala()">
                                <option value="0">Tidak Ada Kendala</option>
                                <option value="1">Ada Kendala</option>
                            </select>
                        </div>
                        
                        <!-- Area ini disembunyikan secara default menggunakan JS -->
                        <div id="kendala_area" style="display: none;" class="col-md-12">
                            <div class="card bg-dark border-danger mb-3">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label text-danger">Keterangan Kendala</label>
                                        <textarea name="pra_ket_kendala" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label text-danger">Evidence Kendala (Opsional)</label>
                                        <input type="file" name="pra_ev_kendala" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-title">Finalisasi</div>
                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Kesimpulan Akhir</label>
                            <textarea name="kesimpulan" class="form-control" rows="4" required placeholder="Tuliskan kesimpulan siaran sore ini..."></textarea>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Simpan Laporan Utama</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Logika Conditional Rendering sesuai Master Blueprint
        function toggleKendala() {
            var selectBox = document.getElementById("pra_kendala_select");
            var kendalaArea = document.getElementById("kendala_area");
            
            if (selectBox.value === "1") {
                kendalaArea.style.display = "block";
            } else {
                kendalaArea.style.display = "none";
            }
        }
    </script>
</body>
</html>