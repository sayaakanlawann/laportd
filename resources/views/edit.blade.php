<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan TD - TVRI Kalsel</title>
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
        <a href="/evidence" class="btn btn-outline-secondary mb-3">← Kembali ke Dashboard</a>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white fw-bold">Edit Laporan Induk TD</h5>
            </div>
            <div class="card-body">
                <!-- FORM MENGARAH KE RUTE UPDATE DENGAN METHOD PUT -->
                <form action="/laporan/{{ $laporan->id }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="section-title">Data Personil & Waktu</div>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Tugas</label>
                            <input type="date" name="tanggal_tugas" class="form-control" value="{{ $laporan->tanggal_tugas }}" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Nama Petugas (TD)</label>
                            <select name="nama_petugas" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Pilih TD --</option>
                                @foreach($td as $p)
                                    <option value="{{ $p->nama }}" {{ $laporan->nama_petugas == $p->nama ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Petugas PDU</label>
                            <select name="pdu_nama" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Pilih PDU --</option>
                                @foreach($pdu as $p)
                                    <option value="{{ $p->nama }}" {{ $laporan->pdu_nama == $p->nama ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Petugas TX</label>
                            <select name="tx_petugas_nama" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Pilih TX --</option>
                                @foreach($tx as $p)
                                    <option value="{{ $p->nama }}" {{ $laporan->tx_petugas_nama == $p->nama ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Kehadiran Kru</label>
                            <select name="kru_lengkap" class="form-select" required>
                                <option value="1" {{ $laporan->kru_lengkap == 1 ? 'selected' : '' }}>Lengkap</option>
                                <option value="0" {{ $laporan->kru_lengkap == 0 ? 'selected' : '' }}>Tidak Lengkap</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Evidence Rutin (Opsional Jika Tidak Diubah)</div>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Alat Studio & Master</label>
                            <input type="file" name="ev_alat_studio" class="form-control mb-1">
                            <small class="text-warning">Kosongkan jika tidak ingin mengubah file.</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pengecekan Jaringan</label>
                            <input type="file" name="ev_jaringan" class="form-control mb-1">
                            <small class="text-warning">Kosongkan jika tidak ingin mengubah file.</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jalur Audio & Video</label>
                            <input type="file" name="ev_jalur_av" class="form-control mb-1">
                            <small class="text-warning">Kosongkan jika tidak ingin mengubah file.</small>
                        </div>
                    </div>

                    <div class="section-title">Status Kendala Pra-Siaran</div>
                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Apakah ada kendala sebelum siaran?</label>
                            <select name="pra_kendala" id="pra_kendala_select" class="form-select" required onchange="toggleKendala()">
                                <option value="0" {{ $laporan->pra_kendala == 0 ? 'selected' : '' }}>Tidak Ada Kendala</option>
                                <option value="1" {{ $laporan->pra_kendala == 1 ? 'selected' : '' }}>Ada Kendala</option>
                            </select>
                        </div>
                        
                        <div id="kendala_area" style="{{ $laporan->pra_kendala == 1 ? 'display: block;' : 'display: none;' }}" class="col-md-12">
                            <div class="card bg-dark border-danger mb-3">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label text-danger">Keterangan Kendala</label>
                                        <textarea name="pra_ket_kendala" class="form-control" rows="3">{{ $laporan->pra_ket_kendala }}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label text-danger">Evidence Kendala Baru (Opsional)</label>
                                        <input type="file" name="pra_ev_kendala" class="form-control mb-1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOK REPEATER SIARAN -->
                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <div>
                                <h5 class="text-white mb-0">⏰ Log Jam Tayang Siaran (Edit)</h5>
                            </div>
                            <button type="button" class="btn btn-sm btn-info fw-bold" id="btn-tambah-siaran">
                                + Tambah Program
                            </button>
                        </div>

                        <div id="siaran-container">
                            <!-- LOOPING DATA SIARAN LAMA -->
                            @foreach($laporan->siarans as $index => $siaran)
                                @php
                                    $waktuValue = \Carbon\Carbon::parse($siaran->jam_tayang)->format('H:i') . '|' . \Carbon\Carbon::parse($siaran->jam_selesai)->format('H:i');
                                @endphp
                                <div class="siaran-item bg-secondary bg-opacity-10 border border-secondary border-opacity-25 p-3 rounded mb-3 position-relative">
                                    
                                    @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 mt-2 me-2 btn-hapus-siaran" style="padding: 2px 8px;" title="Hapus Baris">✕</button>
                                    @endif
                                    
                                    <div class="row g-3 pe-4">
                                        <div class="col-md-2">
                                            <label class="form-label text-muted small">Waktu Siaran</label>
                                            <select name="waktu_siaran[]" class="form-select bg-dark text-white border-secondary waktu-selector" onchange="gantiPilihanProgram(this)" required>
                                                <option value="15:00|15:59" {{ $waktuValue == '15:00|15:59' ? 'selected' : '' }}>15:00 - 15:59</option>
                                                <option value="16:00|16:59" {{ $waktuValue == '16:00|16:59' ? 'selected' : '' }}>16:00 - 16:59</option>
                                                <option value="17:00|17:59" {{ $waktuValue == '17:00|17:59' ? 'selected' : '' }}>17:00 - 17:59</option>
                                                <option value="18:00|18:59" {{ $waktuValue == '18:00|18:59' ? 'selected' : '' }}>18:00 - 18:59</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label class="form-label text-muted small">Nama Program</label>
                                            <select name="nama_program[]" class="form-select bg-dark text-white border-secondary program-selector" onchange="cekCustomProgram(this)" required>
                                                <option value="{{ $siaran->nama_program }}" selected>{{ $siaran->nama_program }}</option>
                                            </select>
                                            <input type="text" name="nama_program_custom[]" class="form-control form-control-sm bg-dark text-white border-warning mt-2 custom-program" style="display: none;" placeholder="Ketik nama acara baru...">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label text-muted small">Jenis Acara</label>
                                            <select name="jenis_acara[]" class="form-select bg-dark text-white border-secondary" required>
                                                <option value="Live Studio 1" {{ $siaran->jenis_acara == 'Live Studio 1' ? 'selected' : '' }}>Live Studio 1</option>
                                                <option value="Live Studio 2" {{ $siaran->jenis_acara == 'Live Studio 2' ? 'selected' : '' }}>Live Studio 2</option>
                                                <option value="Live Studio 3" {{ $siaran->jenis_acara == 'Live Studio 3' ? 'selected' : '' }}>Live Studio 3</option>
                                                <option value="Relay" {{ $siaran->jenis_acara == 'Relay' ? 'selected' : '' }}>Relay</option>
                                                <option value="Record" {{ $siaran->jenis_acara == 'Record' ? 'selected' : '' }}>Record</option>
                                                <option value="Playback" {{ $siaran->jenis_acara == 'Playback' ? 'selected' : '' }}>Playback</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label text-muted small">Status & Kendala</label>
                                            <div class="input-group">
                                                <select name="status_siaran[]" class="form-select bg-dark text-white border-secondary" style="max-width: 120px;" required>
                                                    <option value="Aman" {{ $siaran->status_siaran == 'Aman' ? 'selected' : '' }}>Aman</option>
                                                    <option value="Audio" {{ $siaran->status_siaran == 'Audio' ? 'selected' : '' }}>Audio</option>
                                                    <option value="Video" {{ $siaran->status_siaran == 'Video' ? 'selected' : '' }}>Video</option>
                                                </select>
                                                <input type="text" name="catatan_kendala[]" class="form-control bg-dark text-white border-secondary" placeholder="Catatan kendala" value="{{ $siaran->catatan_kendala }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="section-title">Finalisasi</div>
                    <div class="row mb-4">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Kesimpulan Akhir</label>
                            <textarea name="kesimpulan" class="form-control" rows="4" required>{{ $laporan->kesimpulan }}</textarea>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning text-dark fw-bold px-4">Update Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleKendala() {
            var selectBox = document.getElementById("pra_kendala_select");
            var kendalaArea = document.getElementById("kendala_area");
            kendalaArea.style.display = selectBox.value === "1" ? "block" : "none";
        }

        const dataProgram = @json($programsGrouped);

        function gantiPilihanProgram(selectWaktu) {
            let row = selectWaktu.closest('.row'); 
            let selectProgram = row.querySelector('.program-selector');
            let inputCustom = row.querySelector('.custom-program');
            let waktuTerpilih = selectWaktu.value;

            selectProgram.innerHTML = '<option value="">-- Pilih Program --</option>';

            if (waktuTerpilih && dataProgram[waktuTerpilih]) {
                dataProgram[waktuTerpilih].forEach(prog => {
                    selectProgram.innerHTML += `<option value="${prog.nama_program}">${prog.nama_program}</option>`;
                });
            }
            
            selectProgram.innerHTML += '<option value="Other">Lainnya (Ketik Manual)...</option>';
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

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('siaran-container');
            const btnTambah = document.getElementById('btn-tambah-siaran');

            const templateSiaran = `
                <div class="siaran-item bg-secondary bg-opacity-10 border border-secondary border-opacity-25 p-3 rounded mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 mt-2 me-2 btn-hapus-siaran" style="padding: 2px 8px;">✕</button>
                    
                    <div class="row g-3 pe-4">
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Waktu Siaran</label>
                            <select name="waktu_siaran[]" class="form-select bg-dark text-white border-secondary waktu-selector" onchange="gantiPilihanProgram(this)" required>
                                <option value="">-- Pilih --</option>
                                <option value="15:00|15:59">15:00 - 15:59</option>
                                <option value="16:00|16:59">16:00 - 16:59</option>
                                <option value="17:00|17:59">17:00 - 17:59</option>
                                <option value="18:00|18:59">18:00 - 18:59</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Nama Program</label>
                            <select name="nama_program[]" class="form-select bg-dark text-white border-secondary program-selector" onchange="cekCustomProgram(this)" required>
                                <option value="">-- Pilih Jam Dulu --</option>
                            </select>
                            <input type="text" name="nama_program_custom[]" class="form-control form-control-sm bg-dark text-white border-warning mt-2 custom-program" style="display: none;" placeholder="Ketik nama acara baru...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small">Jenis Acara</label>
                            <select name="jenis_acara[]" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Pilih --</option>
                                <option value="Live Studio 1">Live Studio 1</option>
                                <option value="Live Studio 2">Live Studio 2</option>
                                <option value="Live Studio 3">Live Studio 3</option>
                                <option value="Relay">Relay</option>
                                <option value="Record">Record</option>
                                <option value="Playback">Playback</option>
                            </select>
                        </div>

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

            btnTambah.addEventListener('click', () => {
                container.insertAdjacentHTML('beforeend', templateSiaran);
            });

            container.addEventListener('click', (e) => {
                if(e.target.classList.contains('btn-hapus-siaran')) {
                    e.target.closest('.siaran-item').remove();
                }
            });
        });
    </script>
</body>
</html>