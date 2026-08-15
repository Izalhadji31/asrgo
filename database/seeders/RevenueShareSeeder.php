<?php

namespace Database\Seeders;

use App\Models\RevenueShare;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder idempotent untuk revenue_shares.
 *
 * Logic di RevenueService::createPayout():
 *   1. Cari revenue_share dengan mitra_id spesifik (latest)
 *   2. Kalo gak ada, cari global (mitra_id IS NULL)
 *   3. Kalo gak ada, payout gak dibuat (silent fail)
 *
 * Seeder ini ngisi:
 *   - 1 rule GLOBAL (mitra_id=NULL) sebagai fallback: 20% platform / 80% mitra
 *   - 1 rule per mitra yang ada (persen sama, biar eksplisit)
 *
 * Persen awal sengaja disamain (20/80) sesuai standar commission platform-as-a-service.
 * Admin bisa edit via panel kapan pun.
 *
 * Jalankan: php artisan db:seed --class=RevenueShareSeeder
 */
class RevenueShareSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus rule lawas biar idempotent (post-merge kalo diganti manual)
        // Tapi preserve rule yang admin ubah (skip kalo ada updated_at > created_at)
        $existing = RevenueShare::all();
        foreach ($existing as $rs) {
            // Hanya hapus rule yang belum pernah di-update manual (updated_at == created_at)
            if ($rs->updated_at && $rs->created_at && $rs->updated_at->eq($rs->created_at)) {
                $rs->delete();
            }
        }

        // Rule GLOBAL fallback
        RevenueShare::firstOrCreate(
            ['mitra_id' => null],
            [
                'persen_platform' => 20.00,
                'persen_mitra'    => 80.00,
            ]
        );

        // Rule per mitra yang ada di sistem
        $mitraIds = DB::table('users')
            ->where('role', 'mitra')
            ->pluck('id');

        $createdPerMitra = 0;
        foreach ($mitraIds as $mitraId) {
            $created = RevenueShare::firstOrCreate(
                ['mitra_id' => $mitraId],
                [
                    'persen_platform' => 20.00,
                    'persen_mitra'    => 80.00,
                ]
            );
            if ($created->wasRecentlyCreated) {
                $createdPerMitra++;
            }
        }

        $totalRows = RevenueShare::count();
        $this->command->info("Seeding revenue_shares selesai.");
        $this->command->info("  Global rule (fallback): 20% platform / 80% mitra");
        $this->command->info("  Per-mitra rules dibuat: {$createdPerMitra}");
        $this->command->info("  Total rows: {$totalRows}");
    }
}