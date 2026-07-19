<!DOCTYPE html>
<html>
<head>
    <title>Resume Laporan TD Sore</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; width: 30%; }
        
        /* Area Evidence */
        .evidence-box { display: inline-block; width: 45%; margin-bottom: 15px; border: 1px solid #ccc; padding: 10px; text-align: center; vertical-align: top; }
        .thumb { max-width: 150px; max-height: 150px; margin-top: 10px; border-radius: 4px; object-fit: cover; }
        .link { display: block; margin-top: 5px; color: blue; text-decoration: none; font-size: 10px; word-break: break-all; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">RESUME LAPORAN TD SORE - TVRI KALSEL</div>
        <div>Tanggal Laporan: {{ \Carbon\Carbon::parse($laporan->tanggal_tugas)->format('d F Y') }}</div>
    </div>

    <!-- Data Utama -->
    <table>
        <tr><th>Nama Technical Director</th><td>{{ $laporan->nama_petugas }}</td></tr>
        <tr><th>Petugas PDU</th><td>{{ $laporan->pdu_nama }}</td></tr>
        <tr><th>Petugas Transmisi</th><td>{{ $laporan->tx_petugas_nama }}</td></tr>
        <tr><th>Status Kehadiran Kru</th><td>{{ $laporan->kru_lengkap ? 'Lengkap' : 'Tidak Lengkap' }}</td></tr>
        <tr><th>Kendala Pra-Siaran</th><td>{{ $laporan->pra_kendala ? 'Ada Kendala' : 'Aman' }}</td></tr>
        @if($laporan->pra_kendala)
        <tr><th>Keterangan Kendala</th><td style="color:red;">{{ $laporan->pra_ket_kendala }}</td></tr>
        @endif
        <tr><th>Kesimpulan Akhir</th><td>{{ $laporan->kesimpulan }}</td></tr>
    </table>

    <!-- Tabel Log Jam Tayang -->
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 30px;">Jadwal Jam Tayang Siaran</h3>
    <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #ccc; padding: 8px;">Waktu Siaran</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Program & Jenis Acara</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Status</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Catatan Kendala</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan->siarans as $siaran)
            <tr>
                <td style="border: 1px solid #ccc; padding: 8px; text-align: center; white-space: nowrap;">
                    {{ \Carbon\Carbon::parse($siaran->jam_tayang)->format('H:i') }} - 
                    {{ \Carbon\Carbon::parse($siaran->jam_selesai)->format('H:i') }}
                </td>
                <td style="border: 1px solid #ccc; padding: 8px;">
                    <strong>{{ $siaran->nama_program }}</strong> <br>
                    <span style="font-size: 10px; color: #555;">({{ $siaran->jenis_acara }})</span>
                </td>
                <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">{{ $siaran->status_siaran }}</td>
                <td style="border: 1px solid #ccc; padding: 8px;">{{ $siaran->catatan_kendala ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ========================================== -->
    <!-- PEMBATAS HALAMAN: EVIDENCE DI HALAMAN BARU -->
    <!-- ========================================== -->
    <div style="page-break-before: always; clear: both; padding-top: 20px;"></div>

    <!-- BAGIAN EVIDENCE -->
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">Rangkuman Bukti Evidence</h3>
    <div>
        @if(is_array($laporan->evidence) && count($laporan->evidence) > 0)
            @foreach($laporan->evidence as $ev)
                <div class="evidence-box">
                    <strong>{{ $ev['keterangan'] }}</strong><br>
                    <span style="font-size: 10px; color: #666;">File: {{ $ev['filename'] }}</span><br>
                    
                    <!-- Rendering Gambar Base64 -->
                    @php
                        $imagePath = storage_path('app/public/' . $ev['file_id']);
                        $imageData = '';
                        if(file_exists($imagePath)) {
                            $mime = mime_content_type($imagePath);
                            $data = file_get_contents($imagePath);
                            $imageData = 'data:' . $mime . ';base64,' . base64_encode($data);
                        }
                    @endphp
                    
                    @if($imageData)
                        <img src="{{ $imageData }}" class="thumb" alt="Thumbnail"><br>
                    @else
                        <div style="color: red; font-size: 10px; margin: 10px 0;">[Gambar Fisik Tidak Ditemukan]</div><br>
                    @endif

                    <a href="{{ $ev['link_drive'] }}" class="link" target="_blank">Buka File Lampiran</a>
                </div>
            @endforeach
        @else
            <p>Tidak ada evidence yang dilampirkan.</p>
        @endif
    </div>

</body>
</html>