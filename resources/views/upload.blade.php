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
        <a href="/" class="btn btn-sm btn-outline-secondary fw-bold mb-3">🏠 Kembali ke Lobi</a>
        <div class="card">
            
            <div class="card-header">
                <!-- Judul form akan otomatis berubah jadi TD Pagi atau TD Sore -->
    <h5 class="mb-0 text-white fw-bold">Input Laporan Induk TD {{ ucfirst($shift ?? 'Sore') }}</h5>
    
    <!-- Tombol Kembali ke Lobi -->
    
            </div>
            <div class="card-body">
                <form action="/upload" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- TAMBAHAN BEDAH MIKRO: Input rahasia untuk mengirim shift -->
                    <input type="hidden" name="shift" value="{{ $shift ?? 'sore' }}">
                    
                    <div class="section-title">Data Personil & Waktu</div>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Tugas</label>
                            <input type="date" name="tanggal_tugas" class="form-control" required>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted small">Nama Petugas (TD)</label>
                            <select name="nama_petugas" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Pilih TD --</option>
                                @foreach($td as $p)
                                    <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted small">Petugas PDU</label>
                            <select name="pdu_nama" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Pilih PDU --</option>
                                @foreach($pdu as $p)
                                    <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- BLOK PETUGAS TX REPEATER -->
                        <div class="col-md-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label text-muted small mb-0">Petugas TX (Transmisi)</label>
                                <button type="button" class="btn btn-sm btn-info fw-bold" id="btn-tambah-tx" style="padding: 2px 10px; font-size: 0.75rem;">
                                    + Tambah TX
                                </button>
                            </div>
                            
                            <div id="tx-container">
                                <div class="tx-item d-flex mb-2">
                                    <select name="tx_petugas_nama[]" class="form-select bg-dark text-white border-secondary" required>
                                        <option value="">-- Pilih TX --</option>
                                        @foreach($tx as $p)
                                            <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div style="width: 38px; margin-left: 8px;"></div> 
                                </div>
                            </div>
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
                    <!-- ========================================== -->
            <!-- BLOK REPEATER: DAFTAR JAM TAYANG SIARAN    -->
            <!-- ========================================== -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-end mb-3">
                    <div>
                        <h5 class="text-white mb-0">⏰ Log Jam Tayang Siaran</h5>
                        <small class="text-muted">Tambahkan semua program acara selama shift bertugas.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-info fw-bold" id="btn-tambah-siaran">
                        + Tambah Program
                    </button>
                </div>

                <!-- Wadah Penampung Form Dinamis -->
                <div id="siaran-container">
                    
                    <!-- Baris Form ke-1 (Default, tidak bisa dihapus) -->
                    <div class="siaran-item bg-secondary bg-opacity-10 border border-secondary border-opacity-25 p-3 rounded mb-3">
                        <div class="row g-3 pe-4">
    <!-- 1. Rentang Waktu (2 Kolom) -->
                            <!-- Waktu Siaran -->
    <div class="col-md-2">
        <label class="form-label text-muted small">Waktu Siaran</label>
        <select name="waktu_siaran[]" class="form-select bg-dark text-white border-secondary waktu-selector" onchange="gantiPilihanProgram(this)" required>
        <option value="">-- Pilih --</option>
        
        <!-- LOOPING DINAMIS DARI CONTROLLER -->
        @foreach($programsGrouped as $jam => $programs)
            <option value="{{ $jam }}">{{ str_replace('|', ' - ', $jam) }}</option>
        @endforeach
        
    </select>
    </div>
    
    <!-- Nama Program -->
    <div class="col-md-3">
        <label class="form-label text-muted small">Nama Program</label>
        <select name="nama_program[]" class="form-select bg-dark text-white border-secondary program-selector" onchange="cekCustomProgram(this)" required>
            <option value="">-- Pilih Jam Dulu --</option>
        </select>
        <!-- Input rahasia untuk "Other" -->
        <input type="text" name="nama_program_custom[]" class="form-control form-control-sm bg-dark text-white border-warning mt-2 custom-program" style="display: none;" placeholder="Ketik nama acara baru...">
    </div>
                            <!-- 3. Jenis Acara (2 Kolom) -->
                            <div class="col-md-3">
                                <label class="form-label text-muted small">Jenis Acara</label>
                                <select name="jenis_acara[]" class="form-select bg-dark text-white border-secondary" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Live Studio 1">Live Studio 1</option>
                                    <option value="Live Studio 2">Live Studio 2</option>
                                    <option value="Live Studio 3">Live Studio 3</option>
                                    <option value="Relay">Relay</option>
                                    @if(strtolower($shift) == 'pagi')
                                    <optgroup label="📡 RELAY REGIONAL">
                                        <option value="RELAY JAKARTA">Relay Jakarta</option>
                                        <option value="RELAY KALBAR">Relay Kalbar</option>
                                        <option value="RELAY KALTIM">Relay Kaltim</option>
                                        <option value="RELAY KALTENG">Relay Kalteng</option>
                                        <option value="RELAY KALTARA">Relay Kaltara</option>
                                        </optgroup>
                                    @endif

                                    <option value="Record">Record</option>
                                    <option value="Playback">Playback</option>
                                </select>
                            </div>

                            <!-- 4. Status & Kendala (4 Kolom) -->
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Status & Kendala</label>
                                <div class="input-group">
                                    <select name="status_siaran[]" class="form-select bg-dark text-white border-secondary" style="max-width: 120px;" required>
                                        <option value="Aman">Aman</option>
                                        <option value="Audio">Audio</option>
                                        <option value="Video">Video</option>
                                    </select>
                                    <input type="text" name="catatan_kendala[]" class="form-control bg-dark text-white border-secondary" placeholder="Catatan kendala (Opsional)">
                                </div>
                            </div>
                        </div>
                                            </div>
                    <!-- Akhir Baris ke-1 -->

                </div>
            </div>
            <!-- ========================================== -->
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
        // Logika Conditional Rendering Kendala Pra-Siaran
        function toggleKendala() {
            var selectBox = document.getElementById("pra_kendala_select");
            var kendalaArea = document.getElementById("kendala_area");
            
            if (selectBox.value === "1") {
                kendalaArea.style.display = "block";
            } else {
                kendalaArea.style.display = "none";
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('siaran-container');
            const btnTambah = document.getElementById('btn-tambah-siaran');
            
            

            // Aksi ketika tombol "+ Tambah TX" diklik
            

            
            // Template HTML untuk baris baru (SUDAH BERSIH DARI $programs)
            const templateSiaran = `
                <div class="siaran-item bg-secondary bg-opacity-10 border border-secondary border-opacity-25 p-3 rounded mb-3 position-relative">
                    <!-- Tombol Hapus (X) -->
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 mt-2 me-2 btn-hapus-siaran" style="padding: 2px 8px;" title="Hapus Baris">✕</button>
                    
                    <div class="row g-3 pe-4">
                        <!-- 1. Rentang Waktu (2 Kolom) -->
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Waktu Siaran</label>
                            <select name="waktu_siaran[]" class="form-select bg-dark text-white border-secondary waktu-selector" onchange="gantiPilihanProgram(this)" required>
        <option value="">-- Pilih --</option>
        
        <!-- LOOPING DINAMIS DARI CONTROLLER -->
        @foreach($programsGrouped as $jam => $programs)
            <option value="{{ $jam }}">{{ str_replace('|', ' - ', $jam) }}</option>
        @endforeach
        
    </select>
                        </div>
                        
                        <!-- 2. Nama Program (3 Kolom) -->
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Nama Program</label>
                            <select name="nama_program[]" class="form-select bg-dark text-white border-secondary program-selector" onchange="cekCustomProgram(this)" required>
                                <option value="">-- Pilih Jam Dulu --</option>
                            </select>
                            <!-- Input rahasia untuk "Other" -->
                            <input type="text" name="nama_program_custom[]" class="form-control form-control-sm bg-dark text-white border-warning mt-2 custom-program" style="display: none;" placeholder="Ketik nama acara baru...">
                        </div>

                        <!-- 3. Jenis Acara (2 Kolom) -->
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Jenis Acara</label>
                            <select name="jenis_acara[]" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Pilih --</option>
                                <option value="Live Studio 1">Live Studio 1</option>
                                <option value="Live Studio 2">Live Studio 2</option>
                                <option value="Live Studio 3">Live Studio 3</option>
                                <option value="Relay">Relay</option>
                                @if(strtolower($shift) == 'pagi')
                                    <optgroup label="📡 RELAY REGIONAL">
                                        <option value="RELAY JAKARTA">Relay Jakarta</option>
                                        <option value="RELAY KALBAR">Relay Kalbar</option>
                                        <option value="RELAY KALTIM">Relay Kaltim</option>
                                        <option value="RELAY KALTENG">Relay Kalteng</option>
                                        <option value="RELAY KALTARA">Relay Kaltara</option>
                                        </optgroup>
                                    @endif
                                <option value="Record">Record</option>
                                <option value="Playback">Playback</option>
                            </select>
                        </div>

                        <!-- 4. Status & Kendala (4 Kolom) -->
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Status & Kendala</label>
                            <div class="input-group">
                                <select name="status_siaran[]" class="form-select bg-dark text-white border-secondary" style="max-width: 120px;" required>
                                    <option value="Aman">Aman</option>
                                    <option value="Audio">Audio</option>
                                    <option value="Video">Video</option>
                                </select>
                                <input type="text" name="catatan_kendala[]" class="form-control bg-dark text-white border-secondary" placeholder="Catatan kendala (Opsional)">
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Aksi ketika tombol "+ Tambah Program" diklik
            btnTambah.addEventListener('click', function() {
                container.insertAdjacentHTML('beforeend', templateSiaran);
            });

            // Aksi ketika tombol "✕" (Hapus) diklik
            container.addEventListener('click', function(e) {
                if(e.target.classList.contains('btn-hapus-siaran')) {
                    e.target.closest('.siaran-item').remove();
                }
            });
            // ====================================================
            // LOGIKA REPEATER PETUGAS TX (TRANSMISI)
            // ====================================================
            const txContainer = document.getElementById('tx-container');
            // ====================================================
            // LOGIKA REPEATER PETUGAS TX (TRANSMISI)
            // ====================================================
            
            const btnTambahTx = document.getElementById('btn-tambah-tx');

            // Kita render daftar option TX dari PHP ke Javascript dengan aman
            const txOptions = `
                <option value="">-- Pilih TX --</option>
                @foreach($tx as $p)
                    <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                @endforeach
            `;

            // Template HTML dinamis
            const templateTx = `
                <div class="tx-item d-flex mb-2">
                    <select name="tx_petugas_nama[]" class="form-select bg-dark text-white border-secondary" required>
                        ${txOptions}
                    </select>
                    <button type="button" class="btn btn-outline-danger ms-2 btn-hapus-tx" style="padding: 4px 10px;" title="Hapus Petugas">✕</button>
                </div>
            `;

            // Aksi saat klik Tambah
            btnTambahTx.addEventListener('click', function() {
                txContainer.insertAdjacentHTML('beforeend', templateTx);
            });

            // Aksi saat klik Hapus (X)
            txContainer.addEventListener('click', function(e) {
                if(e.target.classList.contains('btn-hapus-tx')) {
                    e.target.closest('.tx-item').remove();
                }
            });
        });

        // ----------------------------------------------------
        // LOGIKA DYNAMIC DROPDOWN
        // ----------------------------------------------------
        const dataProgram = @json($programsGrouped);

        function gantiPilihanProgram(selectWaktu) {
            let row = selectWaktu.closest('.row'); 
            let selectProgram = row.querySelector('.program-selector');
            let inputCustom = row.querySelector('.custom-program');
            let waktuTerpilih = selectWaktu.value;

            // Reset dropdown program
            selectProgram.innerHTML = '<option value="">-- Pilih Program --</option>';

            // Masukkan data sesuai jam
            if (waktuTerpilih && dataProgram[waktuTerpilih]) {
                dataProgram[waktuTerpilih].forEach(prog => {
                    selectProgram.innerHTML += `<option value="${prog.nama_program}">${prog.nama_program}</option>`;
                });
            }
            
            selectProgram.innerHTML += '<option value="Other">Lainnya (Ketik Manual)...</option>';

            // Sembunyikan input custom
            inputCustom.style.display = 'none';
            inputCustom.value = '';
            inputCustom.removeAttribute('required');
        }

        function cekCustomProgram(selectProgram) {
            let row = selectProgram.closest('.row');
            let inputCustom = row.querySelector('.custom-program');

            if (selectProgram.value === 'Other') {
                inputCustom.style.display = 'block';
                inputCustom.setAttribute('required', 'required');
            } else {
                inputCustom.style.display = 'none';
                inputCustom.value = '';
                inputCustom.removeAttribute('required');
            }
        }
    </script>
</body>
</html>