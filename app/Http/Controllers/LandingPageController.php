<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ikan; 
use App\Models\Peserta; 
use App\Models\WebsiteContent;

class LandingPageController extends Controller
{
    public function index()
    {
        // Ambil statistik realtime dari database yang sudah ada
        $stats = [
            'peserta' => Peserta::count(),
            'tank' => Ikan::whereNotNull('nomor_tank')->count(),
            'kategori' => Ikan::distinct('kategori')->count('kategori'),
            'juri' => User::where('role', 'juri')->count(),
        ];

        // Ambil konten dinamis (bisa di cache nantinya)
        $contents = WebsiteContent::all()->keyBy(function($item) {
            return $item->section . '_' . $item->key;
        })->map(function($item) {
            return $item->value;
        });

        return view('landing.index', compact('stats', 'contents'));
    }

    // API endpoint untuk Live Score & Ranking di Landing Page
    public function getLiveStats()
    {
        return response()->json([
            'peserta' => Peserta::count(),
            'tank' => Ikan::whereNotNull('nomor_tank')->count(),
            'kategori' => Ikan::distinct('kategori')->count('kategori'),
            'juri' => User::where('role', 'juri')->count(),
        ]);
    }
}