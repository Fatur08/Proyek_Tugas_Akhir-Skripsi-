<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    public function create()
    {
        $harini = date("Y-m-d");
        $nik = Auth::guard('karyawan')->user()->nik;
        $cek = DB::table('presensi')->where('tgl_presensi', $harini)->where('nik', $nik)->count();
        return view('presensi.create', compact('cek'));
    }

    public function store(Request $request){
        $nik = Auth::guard('karyawan')->user()->nik;
        $tgl_presensi = date("Y-m-d");
        $jam = date("H:i:s");
        $lokasi = $request->lokasi;
        $image = $request->image;
        $folderpath = "public/uploads/absensi/";
        $formatName = $nik."-".$tgl_presensi;
        $image_parts = explode(";base64",$image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $file = $folderpath . $fileName;
        $data = [
            'nik' => $nik,
            'tgl_presensi' => $tgl_presensi,
            'jam_in' => $jam,
            'foto_in' => $fileName,
            'lokasi_in' => $lokasi
        ];
        $cek = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('nik', $nik)->count();
        if($cek > 0){
            $data_pulang = [
                'jam_out' => $jam,
                'foto_out' => $fileName,
                'lokasi_out' => $lokasi
            ];
            $update = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('nik', $nik)->update($data_pulang);
            if($update){
                echo "success|Terimakasi, Hati Hati Di Jalan Pulang|out";
                Storage::put($file,$image_base64);
            } else {
                echo "error|Maaf Gagal Absen, Hubungi Petugas IT Sekolah|out";
            }
        } else {
            $simpan = DB::table('presensi')->insert($data);
            if($simpan){
                echo "success|Terimakasi, Selamat Belajar Di Kelas|in";
                Storage::put($file,$image_base64);
            } else {
                echo "error|Maaf Gagal Absen, Hubungi Petugas IT Sekolah|in";
            }
        }
    }
}
