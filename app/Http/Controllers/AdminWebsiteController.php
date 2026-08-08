<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebsiteContent;

class AdminWebsiteController extends Controller
{
    public function index()
    {
        $contents = WebsiteContent::all()->keyBy(function($item) {
            return $item->section . '_' . $item->key;
        })->map(function($item) {
            return $item->value;
        });

        // Arahkan ke folder landing
        return view('landing.manage', compact('contents'));
    }

    public function update(Request $request)
    {
        $section = $request->input('section');
        $data = $request->except(['_token', 'section', 'hero_media_file', 'gallery_files', 'sponsor_files', 'kategori_files']);

        // Helper upload → simpan ke public/uploads/landing, kembalikan URL
        $upload = function ($file) {
            $name = time() . '_' . mt_rand(1000, 9999) . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/landing'), $name);
            return asset('uploads/landing/' . $name);
        };

        // Hero media (foto/video) → key "media" agar gabungannya jadi "hero_media" (FIX BUG)
        if ($request->hasFile('hero_media_file')) {
            $data['media'] = $upload($request->file('hero_media_file'));
        }

        // Galeri (multi-file) → section "gallery", key "images"
        if ($request->hasFile('gallery_files')) {
            $existing = trim((string) $request->input('images', ''));
            $urls = $existing === '' ? [] : preg_split('/\r\n|\r|\n/', $existing);
            foreach ((array) $request->file('gallery_files') as $gfile) {
                if ($gfile) $urls[] = $upload($gfile);
            }
            $data['images'] = implode("\n", array_filter(array_map('trim', $urls)));
        }

        // Sponsor (multi-file) → tambah baris "Sponsor | url" ke key "list"
        if ($request->hasFile('sponsor_files')) {
            $existing = trim((string) $request->input('list', ''));
            $lines = $existing === '' ? [] : preg_split('/\r\n|\r|\n/', $existing);
            foreach ((array) $request->file('sponsor_files') as $sfile) {
                if ($sfile) $lines[] = 'Sponsor | ' . $upload($sfile);
            }
            $data['list'] = implode("\n", array_filter(array_map('trim', $lines)));
        }

        // Kategori (multi-file) → tambah baris "Judul | Deskripsi | url" ke key "list"
        if ($request->hasFile('kategori_files')) {
            $existing = trim((string) $request->input('list', ''));
            $lines = $existing === '' ? [] : preg_split('/\r\n|\r|\n/', $existing);
            foreach ((array) $request->file('kategori_files') as $kfile) {
                if ($kfile) $lines[] = 'Kategori Baru | Deskripsi singkat | ' . $upload($kfile);
            }
            $data['list'] = implode("\n", array_filter(array_map('trim', $lines)));
        }

        foreach ($data as $key => $value) {
            WebsiteContent::updateOrCreate(
                ['section' => $section, 'key' => $key],
                ['value' => $value]
            );
        }

        // Respons AJAX (simpan tanpa refresh)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Konten website berhasil diperbarui!']);
        }

        return redirect()->back()->with('success', 'Konten website berhasil diperbarui!');
    }

    // Upload foto via AJAX (dipakai editor per-konten di admin). Kembalikan URL.
    public function uploadMedia(Request $request)
    {
        $urls = [];
        foreach ((array) $request->file('files', []) as $file) {
            if (!$file) continue;
            $name = time() . '_' . mt_rand(1000, 9999) . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/landing'), $name);
            $urls[] = asset('uploads/landing/' . $name);
        }
        return response()->json(['urls' => $urls]);
    }
}