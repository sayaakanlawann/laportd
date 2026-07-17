<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Laporan TD Sore - TVRI Kalsel</title>
    <!-- Bootstrap 5 & Font Inter -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <a href="/upload" class="btn btn-primary shadow-sm">+ Buat Laporan Baru</a>
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
                                <th width="15%">Info Laporan</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="55%">Daftar Evidence (Thumbnail & Link)</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evidences as $index => $item)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                
                                <td>
                                    <div class="text-white fw-medium mb-1">{{ \Carbon\Carbon::parse($item->tanggal_tugas)->format('d M Y') }}</div>
                                    <div class="text-info fw-bold" style="font-size: 0.85rem;">TD: {{ $item->nama_petugas }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">PDU: {{ $item->pdu_nama }}<br>TX: {{ $item->tx_petugas_nama }}</div>
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
                                
                                <!-- KOLOM BARU KHUSUS EVIDENCE -->
                                <td>
                                    @if(is_array($item->evidence) && count($item->evidence) > 0)
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($item->evidence as $ev)
                                                <div class="p-2 border border-secondary border-opacity-25 rounded bg-dark d-flex flex-column" style="width: 140px;">
                                                    <div class="fw-bold text-truncate text-white mb-1" style="font-size: 0.7rem;" title="{{ $ev['keterangan'] }}">{{ $ev['keterangan'] }}</div>
                                                    
                                                    <!-- Thumbnail Fast Load dari Google API -->
                                                    <div class="d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded mb-1" style="height: 70px; width: 100%;">
                                                    <span style="font-size: 1.5rem; filter: grayscale(100%); opacity: 0.7;">📄</span>
                                                    </div>                                                   
                                                    <div class="text-truncate text-muted mb-2" style="font-size: 0.65rem;" title="{{ $ev['filename'] }}">{{ $ev['filename'] }}</div>
                                                    
                                                    <a href="{{ $ev['link_drive'] }}" target="_blank" class="btn btn-outline-info btn-sm py-0 mt-auto" style="font-size: 0.7rem;">🔗 Buka File</a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">Tidak ada evidence dilampirkan.</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-column justify-content-center gap-2">
                                        <a href="/evidence/{{ $item->id }}/download" class="btn btn-success" title="Unduh PDF Resume">📄 Unduh PDF</a>
                                        <form action="/evidence/{{ $item->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus laporan ini beserta semua gambarnya di Google Drive?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">🗑️ Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
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