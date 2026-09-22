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

        // Upload Logo Kiri (Logo Sekolah & Favicon Tab Browser)
        if ($request->hasFile('logo_kiri')) {
            $file = $request->file('logo_kiri');
            $filename = 'logo_kiri.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $setting->logo_kiri = 'uploads/settings/' . $filename;

            // Sinkronkan langsung ke favicon browser (resolusi tajam 128x128 transparan)
            $this->generateFavicon($uploadDir . '/' . $filename);
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

    /**
     * Generate favicon PNG & ICO beresolusi tajam & transparan dari logo sekolah yang diunggah.
     */
    protected function generateFavicon(string $sourcePath): void
    {
        if (!file_exists($sourcePath)) return;

        $info = @getimagesize($sourcePath);
        if (!$info) return;

        $src = null;
        if ($info['mime'] === 'image/png') {
            $src = @imagecreatefrompng($sourcePath);
        } elseif ($info['mime'] === 'image/jpeg') {
            $src = @imagecreatefromjpeg($sourcePath);
        } elseif ($info['mime'] === 'image/webp') {
            $src = @imagecreatefromwebp($sourcePath);
        }

        if (!$src) {
            @copy($sourcePath, public_path('favicon.png'));
            @copy($sourcePath, public_path('favicon.ico'));
            return;
        }

        $origW = imagesx($src);
        $origH = imagesy($src);
        $targetSize = 128;
        $dest = imagecreatetruecolor($targetSize, $targetSize);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefilledrectangle($dest, 0, 0, $targetSize, $targetSize, $transparent);

        $ratio = min($targetSize / $origW, $targetSize / $origH);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);
        $dstX = (int) round(($targetSize - $newW) / 2);
        $dstY = (int) round(($targetSize - $newH) / 2);

        imagecopyresampled($dest, $src, $dstX, $dstY, 0, 0, $newW, $newH, $origW, $origH);
        imagepng($dest, public_path('favicon.png'), 8);
        imagepng($dest, public_path('favicon.ico'), 8);

        imagedestroy($dest);
        imagedestroy($src);
    }
}
