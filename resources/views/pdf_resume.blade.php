<!DOCTYPE html>
<html>
<head>
    <title>Resume Laporan TD {{ ucfirst($laporan->shift ?? 'Sore') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
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

    <div class="header" style="position: relative; text-align: center;">
        <!-- LOGO TVRI DI POJOK KIRI PDF -->
        @php
            $logoPath = public_path('logo-tvri.png');
            $logoBase64 = '';
            if(file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                // Karena SVG adalah text/xml, kita encode ke base64 dengan mime type yang sesuai
                $logoBase64 = 'data:image/png+xml;base64,' . base64_encode($logoData);
            }
        @endphp

        @if($logoBase64)
            <img src="{{ $logoBase64 }}" style="position: absolute; left: 0; top: 0; height: 40px; width: auto;" alt="Logo TVRI">
        @endif
        <!-- Judul Dinamis mengikuti Shift -->
        <div class="title">RESUME LAPORAN TD {{ strtoupper($laporan->shift ?? 'SORE') }} - TVRI KALSEL</div>
        <div>Tanggal Laporan: {{ \Carbon\Carbon::parse($laporan->tanggal_tugas)->format('d F Y') }}</div>
    </div>

    <!-- Data Utama -->
    <table>
        <tr><th>Nama Technical Director</th><td>{{ $laporan->nama_petugas }}</td></tr>
        <tr><th>Petugas PDU</th><td>{{ $laporan->pdu_nama }}</td></tr>
        <tr>
            <th>Asisten PDU</th>
            <td>{{ $laporan->asisten_pdu ?? 'Tidak ada asisten PDU' }}</td>
        </tr>
        <!-- Memecah array TX menjadi teks yang dipisahkan koma -->
        <tr>
            <th>Petugas Transmisi</th>
            <td>
                {{ is_array($laporan->tx_petugas_nama) ? implode(', ', $laporan->tx_petugas_nama) : $laporan->tx_petugas_nama }}
            </td>
        </tr>
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
    <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 15px;">Rangkuman Bukti Evidence</h3>

    @php
        // Helper untuk merapikan data gambar (baik format array baru maupun string/array lama)
        $collectImages = function($labelSection, $images) {
            $results = [];
            if (empty($images)) return $results;

            if (is_string($images)) {
                $decoded = json_decode($images, true);
                $images = is_array($decoded) ? $decoded : [$images];
            }

            if (is_array($images)) {
                foreach ($images as $index => $item) {
                    $path = '';
                    $caption = '';

                    if (is_array($item)) {
                        $path = $item['path'] ?? $item['file_id'] ?? reset($item);
                        $caption = $item['keterangan'] ?? $item['caption'] ?? '';
                    } else {
                        $path = $item;
                    }

                    if (!empty($path) && is_string($path)) {
                        $imgNum = $index + 1;
                        $finalCaption = !empty($caption) ? $caption : "Gambar {$imgNum} : {$labelSection}";
                        $results[] = [
                            'path' => $path,
                            'caption' => $finalCaption,
                            'section' => $labelSection
                        ];
                    }
                }
            }
            return $results;
        };

        // Kumpulkan semua evidence dari berbagai kolom baru dan arsip lama
        $allEvidences = array_merge(
            $collectImages('Sebelum Siaran', $laporan->evidence_sebelum_siaran),
            $collectImages('Alat & Master', $laporan->ev_alat_studio),
            $collectImages('Jaringan', $laporan->ev_jaringan),
            $collectImages('Jalur AV', $laporan->ev_jalur_av),
            $collectImages('Evidence Kendala', $laporan->pra_ev_kendala),
            $collectImages('Arsip Evidence (Lama)', $laporan->evidence ?? []),
            $collectImages('Arsip Link (Lama)', $laporan->link_drive ?? [])
        );
    @endphp

    @if(count($allEvidences) > 0)
        <div>
            @foreach($allEvidences as $ev)
                <div class="evidence-box">
                    <!-- Label Keterangan / Kategori -->
                    <strong>{{ $ev['caption'] }}</strong><br>
                    <span style="font-size: 9px; color: #666; word-break: break-all;">Path: {{ basename($ev['path']) }}</span><br>
                    
                    <!-- Rendering Gambar Base64 untuk DOMPDF -->
                    @php
                        // Cek apakah path sudah berupa URL mutlak atau path storage lokal
                        $cleanPath = str_replace(asset('storage/'), '', $ev['path']);
                        $imagePath = storage_path('app/public/' . $cleanPath);
                        
                        // Jika path aslinya adalah URL eksternal/full
                        if (str_starts_with($ev['path'], 'http')) {
                            $imagePath = str_replace(url('/storage/'), storage_path('app/public/'), $ev['path']);
                        }

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
                        <div style="color: red; font-size: 10px; margin: 15px 0;">[File Gambar Fisik Tidak Ditemukan]</div><br>
                    @endif

                    <a href="{{ str_starts_with($ev['path'], 'http') ? $ev['path'] : asset('storage/' . $ev['path']) }}" class="link" target="_blank">Buka File Asli</a>
                </div>
            @endforeach
        </div>
    @else
        <p>Tidak ada evidence yang dilampirkan.</p>
    @endif

</body>
</html>