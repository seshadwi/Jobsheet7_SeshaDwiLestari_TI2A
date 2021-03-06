<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\Mahasiswa_MataKuliah;
use Illuminate\Support\Facades\Storage;
use PDF;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $mahasiswa = DB::table('mahasiswa')->get();
        $mahasiswa = Mahasiswa::orderBy('Nim', 'asc')->paginate(3);
        return view('mahasiswa.index', compact('mahasiswa'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kelas = Kelas::all();
        return view('mahasiswa.create', ['kelas' => $kelas]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'Nim' => 'required',
            'Nama' => 'required',
            'Kelas' => 'required',
            'Jurusan' => 'required',
            'Email' => 'required',
            'Alamat' => 'required',
            'TTL' => 'required',
            'image' => 'required',
        ]);
        if ($request->file('image')) {
            $image_name = $request->file('image')->store('images', 'public');
        }
        $mahasiswa = new Mahasiswa();
        $mahasiswa->nim = $request->get('Nim');
        $mahasiswa->nama = $request->get('Nama');
        $mahasiswa->kelas_id = $request->get('Kelas');
        $mahasiswa->jurusan = $request->get('Jurusan');
        $mahasiswa->email = $request->get('Email');
        $mahasiswa->alamat = $request->get('Alamat');
        $mahasiswa->ttl = $request->get('TTL');
        $mahasiswa->foto = $image_name;
        $mahasiswa->save();

        $kelas = new Kelas();
        $kelas->id = $request->get('Kelas');

        $mahasiswa->kelas()->associate($kelas);
        $mahasiswa->save();

        print_r($request->all());
        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $nim)
    {
        $nama = $request->get('q');
        if ($nama == null) {
            $Mahasiswa = Mahasiswa::with('kelas')->where('nim', $nim)->first();
        } else {
            $Mahasiswa = Mahasiswa::with('kelas')->where('nama', $nama)->first();
        }
        return view('mahasiswa.detail', compact('Mahasiswa'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($nim)
    {
        $kelas = Kelas::all();
        $Mahasiswa = Mahasiswa::with('kelas')->where('nim', $nim)->first();
        return view('mahasiswa.edit', compact('Mahasiswa', 'kelas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $nim)
    {
        $request->validate([
            'Nim' => 'required',
            'Nama' => 'required',
            'Kelas' => 'required',
            'Jurusan' => 'required',
            'Email' => 'required',
            'Alamat' => 'required',
            'TTL' => 'required',
            'image' => 'required'
        ]);

        $mahasiswa = Mahasiswa::with('kelas')->where('nim', $nim)->first();
        $mahasiswa->nim = $request->get('Nim');
        $mahasiswa->nama = $request->get('Nama');
        $mahasiswa->kelas_id = $request->get('Kelas');
        $mahasiswa->jurusan = $request->get('Jurusan');
        $mahasiswa->email = $request->get('Email');
        $mahasiswa->alamat = $request->get('Alamat');
        $mahasiswa->ttl = $request->get('TTL');

        if ($mahasiswa->foto && file_exists(storage_path('app/public/' . $mahasiswa->foto))) {
            Storage::delete('public/' . $mahasiswa->foto);
        }
        $mahasiswa->foto = $request->file('image')->store('images', 'public');
        $mahasiswa->save();

        $kelas = new Kelas();
        $kelas->id = $request->get('Kelas');

        $mahasiswa->kelas()->associate($kelas);
        $mahasiswa->save();

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa Berhasil Diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($nim)
    {
        Mahasiswa::where('nim', $nim)->delete();
        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa Berhasil Dihapus');
    }
    public function nilai($nim)
    {
        $mahasiswa = Mahasiswa::with('kelas')->where('nim', $nim)->first();
        $nilai = Mahasiswa_MataKuliah::with('matakuliah')->where('mahasiswa_id', $mahasiswa->id_mahasiswa)->get();
        return view('mahasiswa.nilai', compact('mahasiswa', 'nilai'));
    }
    public function print_nilai($nim)
    {

        $mahasiswa = Mahasiswa::with('kelas')->where('nim', $nim)->first();
        $nilai = Mahasiswa_MataKuliah::with('matakuliah')->where('mahasiswa_id', $mahasiswa->id_mahasiswa)->get();
        $pdf = PDF::loadview('mahasiswa.nilai_pdf', ['mahasiswa' => $mahasiswa, 'nilai' => $nilai]);
        return $pdf->stream();
    }
}
