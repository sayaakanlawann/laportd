<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petugas;
use App\Models\ProgramSiaran; // Pastikan ini ada

class MasterDataController extends Controller
{
    // 1. Menampilkan Halaman Master Data (Ditambah data program)
    public function index()
    {
        $petugas = Petugas::orderBy('nama')->get();
        $programs = ProgramSiaran::orderBy('nama_program')->get(); // Tambahan baru
        
        return view('master_data', compact('petugas', 'programs')); // Bawa kedua data
    }

    // 2. Fungsi AJAX Update Petugas (Tetap sama)
    public function updatePetugas(Request $request)
    {
        $petugas = Petugas::find($request->id);
        if($petugas) {
            $petugas->{$request->column} = $request->value;
            $petugas->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // 3. Fungsi AJAX Update Program Siaran (BARU)
    public function updateProgram(Request $request)
    {
        $program = ProgramSiaran::find($request->id);
        if($program) {
            $program->{$request->column} = $request->value;
            $program->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // 4. Menyimpan Petugas Baru
    public function storePetugas(Request $request)
    {
        $petugas = new Petugas();
        $petugas->nama = $request->nama;
        $petugas->jabatan_utama = $request->jabatan_utama;
        $petugas->save(); // Otomatis is_aktif = true berkat tabel database kita
        
        return back()->with('success', 'Petugas baru berhasil ditambahkan!');
    }

    // 5. Menyimpan Program Siaran Baru
    public function storeProgram(Request $request)
    {
        $program = new ProgramSiaran();
        $program->nama_program = $request->nama_program;
        $program->jam_tayang_default = $request->jam_tayang_default;
        $program->save();
        
        return back()->with('success', 'Program siaran baru berhasil ditambahkan!');
    }
}