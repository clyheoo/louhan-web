<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use App\Models\ContestCategory;
use App\Models\ContestClass;

/**
 * Sumber tunggal daftar Kategori & Kelas kontes.
 *
 * Semua method punya FALLBACK ke nilai lama (hardcode) bila tabel belum ada
 * (mis. sebelum migration dijalankan), sehingga aman menggantikan literal
 * ['Bonsai','Jumbo'] / daftar kategori di seluruh kode tanpa mengubah perilaku.
 */
class Taxonomy
{
    const CACHE_CAT = 'taxonomy_categories_v1';
    const CACHE_CLS = 'taxonomy_classes_v1';

    private static function fallbackCategories(): array
    {
        return [
            ['name' => 'Cencu',       'uses_kelas' => true],
            ['name' => 'Chingwa',     'uses_kelas' => true],
            ['name' => 'Freemarking', 'uses_kelas' => true],
            ['name' => 'Goldenbase',  'uses_kelas' => true],
            ['name' => 'Klasik',      'uses_kelas' => true],
            ['name' => 'Bonsai',      'uses_kelas' => false],
            ['name' => 'Jumbo',       'uses_kelas' => false],
        ];
    }

    /** @return array<int,array{name:string,uses_kelas:bool}> */
    public static function categories(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_CAT, function () {
                $rows = ContestCategory::orderBy('urutan')->orderBy('name')->get(['name', 'uses_kelas']);
                if ($rows->isEmpty()) {
                    return self::fallbackCategories();
                }
                return $rows->map(fn ($c) => [
                    'name'       => $c->name,
                    'uses_kelas' => (bool) $c->uses_kelas,
                ])->toArray();
            });
        } catch (\Throwable $e) {
            return self::fallbackCategories();
        }
    }

    /** Semua nama kategori (urut). */
    public static function categoryNames(): array
    {
        return array_values(array_map(fn ($c) => $c['name'], self::categories()));
    }

    /** Kategori TANPA kelas (pengganti literal ['Bonsai','Jumbo']). */
    public static function noKelasNames(): array
    {
        return array_values(array_map(
            fn ($c) => $c['name'],
            array_filter(self::categories(), fn ($c) => !$c['uses_kelas'])
        ));
    }

    /** Kategori yang MEMAKAI kelas. */
    public static function withKelasNames(): array
    {
        return array_values(array_map(
            fn ($c) => $c['name'],
            array_filter(self::categories(), fn ($c) => $c['uses_kelas'])
        ));
    }

    /** Semua nama kelas (urut). */
    public static function classNames(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_CLS, function () {
                $names = ContestClass::orderBy('urutan')->orderBy('name')->pluck('name')->toArray();
                return empty($names) ? ['A', 'B', 'C', 'D', 'E'] : $names;
            });
        } catch (\Throwable $e) {
            return ['A', 'B', 'C', 'D', 'E'];
        }
    }

    /** Panggil setiap kali kategori/kelas diubah. */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_CAT);
        Cache::forget(self::CACHE_CLS);
    }
}