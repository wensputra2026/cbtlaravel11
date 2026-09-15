<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolSettingController extends Controller
{
    /**
     * Halaman Pengaturan Identitas Sekolah & Aplikasi CBT.
     */
    public function index(): View
    {
        $setting = Setting::current();
        return view('admin.setting.identitas', compact('setting'));
    }

    /**
     * Simpan Pembaruan Identitas Sekolah & Berkas Logo / Favicon.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'sekolah'       => 'required|string|max:150',
            'nama_aplikasi' => 'required|string|max:100',
            'kepsek'        => 'nullable|string|max:100',
            'nip'           => 'nullable|string|max:50',
            'alamat'        => 'nullable|string|max:255',
            'kota'          => 'nullable|string|max:100',
            'logo_kiri'     => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'logo_kanan'    => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'background'    => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'tandatangan'   => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $setting = Setting::first() ?? new Setting();

        $setting->sekolah       = $request->input('sekolah');
        $setting->nama_aplikasi = $request->input('nama_aplikasi');
        $setting->kepsek        = $request->input('kepsek');
        $setting->nip           = $request->input('nip');
        $setting->alamat        = $request->input('alamat');
        $setting->kota          = $request->input('kota');
        $setting->npsn          = $request->input('npsn', $setting->npsn);
        $setting->telp          = $request->input('telp', $setting->telp);

        $uploadDir = public_path('uploads/settings');
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        // Upload Logo Kiri
        if ($request->hasFile('logo_kiri')) {
            $file = $request->file('logo_kiri');
            $filename = 'logo_kiri.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $setting->logo_kiri = 'uploads/settings/' . $filename;
            @copy($uploadDir . '/' . $filename, public_path('favicon.png'));
            @copy($uploadDir . '/' . $filename, public_path('favicon.ico'));
        }

        // Upload Logo Kanan
        if ($request->hasFile('logo_kanan')) {
            $file = $request->file('logo_kanan');
            $filename = 'logo_kanan.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $setting->logo_kanan = 'uploads/settings/' . $filename;
        }

        // Upload Background
        if ($request->hasFile('background')) {
            $file = $request->file('background');
            $filename = 'background.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $setting->background = 'uploads/settings/' . $filename;
        }

        // Upload Tanda Tangan
        if ($request->hasFile('tandatangan')) {
            $file = $request->file('tandatangan');
            $filename = 'tandatangan.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $setting->tandatangan = 'uploads/settings/' . $filename;
        }

        $setting->save();

        return back()->with('success', 'Pengaturan identitas sekolah, logo, dan nama aplikasi berhasil diperbarui seketika.');
    }
}
