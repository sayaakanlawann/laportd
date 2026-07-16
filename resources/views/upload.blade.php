<!DOCTYPE html>
<html lang="id">
<head>
    <title>Upload Evidence TVRI</title>
</head>
<body style="font-family: sans-serif; padding: 2rem;">
    
    <h2>Upload Dokumen Evidence TVRI</h2>
    
    <!-- TAMBAHKAN KODE INI UNTUK MENAMPILKAN PESAN -->
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
    <!-- BATAS TAMBAHAN -->

    <form action="/upload" method="POST" enctype="multipart/form-data">
    <!-- enctype wajib ada agar form bisa mengirim file fisik -->
    <form action="/upload" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div style="margin-bottom: 1rem;">
            <label>Judul Dokumen:</label><br>
            <input type="text" name="judul" required style="padding: 5px; width: 300px;">
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label>Pilih File (PDF/Gambar/dll):</label><br>
            <input type="file" name="file" required>
        </div>
        
        <button type="submit" style="padding: 10px 20px; background: blue; color: white; border: none; cursor: pointer;">
            Upload ke Google Drive
        </button>
    </form>
</body>
</html>