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
        .evidence-box { display: inline-block; width: 45%; margin-bottom: 15px; border: 1px solid #ccc; padding: 10px; text-align: center; }
        .thumb { max-width: 150px; max-height: 150px; margin-top: 10px; border-radius: 4px; }
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

    <!-- Data Evidence Gabungan (Looping JSON) -->
    <div style="margin-top: 20px;">
        <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">Rangkuman Bukti Evidence</h3>
        
        @if(is_array($laporan->evidence) && count($laporan->evidence) > 0)
            @foreach($laporan->evidence as $ev)
                <div class="evidence-box">
                    <strong>{{ $ev['keterangan'] }}</strong><br>
                    <span style="font-size: 10px; color: #666;">File: {{ $ev['filename'] }}</span><br>
                    
                    <!-- Thumbnail rahasia dari Google API -->
                    
                    <!-- Link Asli Google Drive -->
                    <a href="{{ $ev['link_drive'] }}" class="link" target="_blank">Buka di Google Drive</a>
                </div>
            @endforeach
        @else
            <p>Tidak ada evidence yang dilampirkan.</p>
        @endif
    </div>

</body>
</html>