<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Evidence TVRI</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .btn { padding: 8px 15px; background: blue; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-danger { background: red; border: none; cursor: pointer; }
    </style>
</head>
<body style="font-family: sans-serif; padding: 2rem;">
    
    <h2>Daftar Dokumen Evidence TVRI</h2>
    
    <!-- Area untuk menampilkan pesan sukses/error -->
    @if(session('success'))
        <div style="padding: 10px; background-color: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding: 10px; background-color: #f8d7da; color: #721c24; margin-bottom: 15px; border-radius: 5px;">
            {{ session('error') }}
        </div>
    @endif

    <a href="/upload" class="btn">Tambah Dokumen Baru</a>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Dokumen</th>
                <th>Nama File</th>
                <th>Waktu Upload</th>
                <th>Aksi</th> <!-- Tambahan Kolom Baru -->
            </tr>
        </thead>
        <tbody>
            @forelse($evidences as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->judul }}</td>
                <td>{{ $item->file_path }}</td>
                <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                <td>
                    <!-- Wadah agar tombol berjejer rapi ke samping -->
                    <div style="display: flex; gap: 10px;">
                        
                        <!-- Tombol Download (Warna Hijau) -->
                        <a href="/evidence/{{ $item->id }}/download" class="btn" style="background: #28a745;">Download</a>

                        <!-- Tombol Hapus (Warna Merah) yang sudah ada -->
                        <form action="/evidence/{{ $item->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                        
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">Belum ada dokumen yang diupload.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>