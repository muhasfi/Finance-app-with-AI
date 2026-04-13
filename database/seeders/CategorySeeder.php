<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // ── PENGELUARAN ──────────────────────────────────────────
            ['name' => 'Makanan & Minuman', 'type' => 'expense', 'color' => '#f97316', 'icon' => 'bi-cup-hot',       'sort_order' => 1,
             'children' => [
                 ['name' => 'Makan siang',  'color' => '#fb923c', 'icon' => 'bi-cup-hot'],
                 ['name' => 'Kopi & snack', 'color' => '#fb923c', 'icon' => 'bi-cup-hot'],
                 ['name' => 'Groceries',    'color' => '#fb923c', 'icon' => 'bi-bag'],
                 ['name' => 'Restoran',     'color' => '#fb923c', 'icon' => 'bi-cup-hot'],
             ]],
            ['name' => 'Transportasi',      'type' => 'expense', 'color' => '#3b82f6', 'icon' => 'bi-car-front',     'sort_order' => 2,
             'children' => [
                 ['name' => 'Bensin',           'color' => '#60a5fa', 'icon' => 'bi-fuel-pump'],
                 ['name' => 'Ojek online',      'color' => '#60a5fa', 'icon' => 'bi-phone'],
                 ['name' => 'Parkir & tol',     'color' => '#60a5fa', 'icon' => 'bi-p-square'],
                 ['name' => 'Servis kendaraan', 'color' => '#60a5fa', 'icon' => 'bi-wrench'],
             ]],
            ['name' => 'Belanja',           'type' => 'expense', 'color' => '#ec4899', 'icon' => 'bi-bag',           'sort_order' => 3,
             'children' => [
                 ['name' => 'Pakaian',    'color' => '#f472b6', 'icon' => 'bi-bag'],
                 ['name' => 'Elektronik', 'color' => '#f472b6', 'icon' => 'bi-laptop'],
                 ['name' => 'Perabotan',  'color' => '#f472b6', 'icon' => 'bi-house'],
             ]],
            ['name' => 'Tagihan',           'type' => 'expense', 'color' => '#eab308', 'icon' => 'bi-lightning',     'sort_order' => 4,
             'children' => [
                 ['name' => 'Listrik',   'color' => '#facc15', 'icon' => 'bi-lightning'],
                 ['name' => 'Air',       'color' => '#facc15', 'icon' => 'bi-droplet'],
                 ['name' => 'Internet',  'color' => '#facc15', 'icon' => 'bi-wifi'],
                 ['name' => 'Telepon',   'color' => '#facc15', 'icon' => 'bi-phone'],
                 ['name' => 'Streaming', 'color' => '#facc15', 'icon' => 'bi-play-circle'],
             ]],
            ['name' => 'Kesehatan',         'type' => 'expense', 'color' => '#ef4444', 'icon' => 'bi-heart-pulse',   'sort_order' => 5,
             'children' => [
                 ['name' => 'Dokter & RS', 'color' => '#f87171', 'icon' => 'bi-hospital'],
                 ['name' => 'Obat-obatan', 'color' => '#f87171', 'icon' => 'bi-capsule'],
                 ['name' => 'Olahraga',    'color' => '#f87171', 'icon' => 'bi-trophy'],
             ]],
            ['name' => 'Pendidikan',        'type' => 'expense', 'color' => '#8b5cf6', 'icon' => 'bi-book',          'sort_order' => 6,
             'children' => [
                 ['name' => 'Kursus & les', 'color' => '#a78bfa', 'icon' => 'bi-mortarboard'],
                 ['name' => 'Buku',         'color' => '#a78bfa', 'icon' => 'bi-book'],
             ]],
            ['name' => 'Hiburan',           'type' => 'expense', 'color' => '#06b6d4', 'icon' => 'bi-film',          'sort_order' => 7,
             'children' => [
                 ['name' => 'Bioskop', 'color' => '#22d3ee', 'icon' => 'bi-film'],
                 ['name' => 'Game',    'color' => '#22d3ee', 'icon' => 'bi-controller'],
                 ['name' => 'Liburan', 'color' => '#22d3ee', 'icon' => 'bi-map'],
             ]],
            ['name' => 'Tempat Tinggal',    'type' => 'expense', 'color' => '#14b8a6', 'icon' => 'bi-house',         'sort_order' => 8,
             'children' => [
                 ['name' => 'Sewa / KPR', 'color' => '#2dd4bf', 'icon' => 'bi-house'],
                 ['name' => 'Renovasi',   'color' => '#2dd4bf', 'icon' => 'bi-tools'],
             ]],
            ['name' => 'Sosial',            'type' => 'expense', 'color' => '#f59e0b', 'icon' => 'bi-people',        'sort_order' => 9,
             'children' => [
                 ['name' => 'Hadiah',  'color' => '#fbbf24', 'icon' => 'bi-gift'],
                 ['name' => 'Donasi',  'color' => '#fbbf24', 'icon' => 'bi-heart'],
                 ['name' => 'Arisan',  'color' => '#fbbf24', 'icon' => 'bi-people'],
             ]],
            ['name' => 'Lain-lain',         'type' => 'expense', 'color' => '#6b7280', 'icon' => 'bi-three-dots',    'sort_order' => 10],

            // ── PEMASUKAN ────────────────────────────────────────────
            ['name' => 'Gaji & Upah',       'type' => 'income',  'color' => '#22c55e', 'icon' => 'bi-cash-stack',    'sort_order' => 1,
             'children' => [
                 ['name' => 'Gaji pokok', 'color' => '#4ade80', 'icon' => 'bi-cash'],
                 ['name' => 'Bonus',      'color' => '#4ade80', 'icon' => 'bi-cash'],
                 ['name' => 'Lembur',     'color' => '#4ade80', 'icon' => 'bi-clock'],
             ]],
            ['name' => 'Bisnis & Freelance', 'type' => 'income', 'color' => '#10b981', 'icon' => 'bi-briefcase',     'sort_order' => 2,
             'children' => [
                 ['name' => 'Penjualan', 'color' => '#34d399', 'icon' => 'bi-bag-check'],
                 ['name' => 'Jasa',      'color' => '#34d399', 'icon' => 'bi-wrench'],
             ]],
            ['name' => 'Pendapatan Lain',    'type' => 'income',  'color' => '#6b7280', 'icon' => 'bi-three-dots',   'sort_order' => 3],
        ];

        foreach ($categories as $data) {
            $children = $data['children'] ?? [];
            unset($data['children']);

            $parent = Category::create([
                'id'         => Str::uuid(),
                'user_id'    => null,
                'parent_id'  => null,
                'is_default' => true,
                ...$data,
            ]);

            foreach ($children as $i => $child) {
                Category::create([
                    'id'         => Str::uuid(),
                    'user_id'    => null,
                    'parent_id'  => $parent->id,
                    'type'       => $data['type'],
                    'is_default' => true,
                    'sort_order' => $i,
                    ...$child,
                ]);
            }
        }
    }
}
