<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Laporan TD Sore - TVRI Kalsel</title>
    <!-- Bootstrap 5 & Font Inter -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #0f172a; font-family: 'Inter', sans-serif; color: #cbd5e1; }
        .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); }
        .card-header { border-bottom: 1px solid #334155; background: transparent; padding: 1.5rem; }
        .card-body { padding: 1.5rem; }
        .table { border-color: #334155; color: #cbd5e1; margin-bottom: 0; }
        .table th { background-color: #0f172a; color: #f8fafc; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; padding: 1rem; border-bottom: 2px solid #334155; }
        .table td { border-color: #334155; padding: 1rem; vertical-align: middle; font-size: 0.875rem; }
        .table-hover tbody tr:hover { background-color: #334155; transition: all 0.2s; }
        
        .btn { border-radius: 8px; font-weight: 500; padding: 0.5rem 1rem; font-size: 0.8125rem; transition: all 0.2s; }
        .btn-primary { background-color: #6366f1; border: none; color: white; }
        .btn-primary:hover { background-color: #4f46e5; transform: translateY(-1px); }
        .btn-success { background-color: #10b981; border: none; color: white; }
        .btn-success:hover { background-color: #059669; }
        .btn-danger { background-color: #e11d48; border: none; color: white; }
        .btn-danger:hover { background-color: #be123c; }
        
        .badge-status { font-weight: 500; padding: 0.4em 0.8em; border-radius: 6px; }
    </style>
</head>
<body>
    
    <div class="container-fluid mt-5 mb-5" style="max-width: 1200px;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0 text-white fw-bold">Laporan <span style="color: #6366f1;">TD Sore</span></h4>
    
    <!-- Area Tombol -->
    <div>
        <a href="/export-excel" class="btn btn-success shadow-sm me-2">📊 Export Excel</a>
        <a href="/upload" class="btn btn-primary shadow-sm">+ Buat Laporan Baru</a>
    </div>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-success d-flex align-items-center" role="alert">
                        <span class="me-2">✓</span> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger d-flex align-items-center" role="alert">
                        <span class="me-2">⚠</span> {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive rounded border border-secondary border-opacity-25">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="45%">Info & Log Siaran</th>
                                <th width="15%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Evidence</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evidences as $index => $item)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                
                                <td>
                                    <!-- INFO LAPORAN -->
                                    <div class="text-white fw-medium mb-1">{{ \Carbon\Carbon::parse($item->tanggal_tugas)->format('d M Y') }}</div>
                                    <div class="text-info fw-bold" style="font-size: 0.85rem;">TD: {{ $item->nama_petugas }}</div>
                                    <div class="text-muted mb-2" style="font-size: 0.75rem;">PDU: {{ $item->pdu_nama }} | TX: {{ $item->tx_petugas_nama }}</div>
                                    
                                    <!-- LOG SIARAN DIJADIKAN SATU KOLOM -->
                                    <div class="mt-2 pt-2 border-top border-secondary border-opacity-25">
                                        <small class="text-white fw-bold">Log Siaran:</small>
                                        <ul class="list-unstyled mb-0 mt-1" style="font-size: 0.75rem;">
                                            @foreach($item->siarans as $siaran)
                                                <li class="text-muted mb-1">
                                                    <span class="text-info fw-bold">{{ \Carbon\Carbon::parse($siaran->jam_tayang)->format('H:i') }} - {{ \Carbon\Carbon::parse($siaran->jam_selesai)->format('H:i') }}</span> | {{ $siaran->nama_program }} 
                                                    <span class="badge {{ $siaran->status_siaran == 'Aman' ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.6rem;">
                                                        {{ $siaran->status_siaran }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                                
                                <td class="text-center">
                                    <div class="mb-2">
                                        @if($item->kru_lengkap)
                                            <span class="badge badge-status bg-success bg-opacity-10 text-success border border-success border-opacity-25 w-100">Kru Lengkap</span>
                                        @else
                                            <span class="badge badge-status bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 w-100">Kru Kurang</span>
                                        @endif
                                    </div>
                                    <div>
                                        @if($item->pra_kendala)
                                            <span class="badge badge-status bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 w-100">Ada Kendala</span>
                                        @else
                                            <span class="badge badge-status bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 w-100">Aman</span>
                                        @endif
                                    </div>
                                </td>
                               
                                <td class="text-center align-middle">
                                    <!-- TOMBOL PEMANGGIL MODAL POP-UP -->
                                    <button type="button" class="btn btn-sm btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#modalEvidence-{{ $item->id }}">
                                        🖼️ Lihat Evidence
                                    </button>
                                </td>

                                <td>
                                    <div class="d-flex flex-column justify-content-center gap-2">
                                        <a href="/evidence/{{ $item->id }}/download" class="btn btn-success" title="Unduh PDF Resume">📄 Unduh PDF</a>
                                        <form action="/evidence/{{ $item->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus laporan ini beserta semua file lokalnya?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">🗑️ Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- ========================================== -->
                            <!-- MODAL POP-UP EVIDENCE (Sembunyi by default)-->
                            <!-- ========================================== -->
                            <div class="modal fade" id="modalEvidence-{{ $item->id }}" tabindex="-1" aria-labelledby="modalLabel-{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content bg-dark text-white border-secondary">
                                        <div class="modal-header border-secondary border-opacity-25">
                                            <h5 class="modal-title" id="modalLabel-{{ $item->id }}">
                                                🖼️ Bukti Evidence - TD: {{ $item->nama_petugas }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        
                                        <div class="modal-body">
                                            @if(is_array($item->evidence) && count($item->evidence) > 0)
                                                <div class="row g-3">
                                                    @foreach($item->evidence as $ev)
                                                        <div class="col-md-6">
                                                            <div class="card bg-secondary bg-opacity-10 border-secondary border-opacity-25 h-100">
                                                                <img src="{{ $ev['link_drive'] }}" class="card-img-top" alt="Evidence" style="height: 200px; object-fit: cover;">
                                                                <div class="card-body p-2 text-center">
                                                                    <p class="card-text small mb-2 fw-bold text-info">{{ $ev['keterangan'] }}</p>
                                                                    <a href="{{ $ev['link_drive'] }}" target="_blank" class="btn btn-sm btn-primary py-1 px-3">
                                                                        Buka File Penuh
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-center text-muted my-3">Tidak ada file evidence yang dilampirkan pada laporan ini.</p>
                                            @endif
                                        </div>
                                        
                                        <div class="modal-footer border-secondary border-opacity-25">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ========================================== -->

                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="mb-2" style="font-size: 2rem;">📂</div>
                                    Belum ada data laporan yang diunggah.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</body>
</html>