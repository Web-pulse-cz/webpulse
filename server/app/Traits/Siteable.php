<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait Siteable
{
    /**
     * Save sites to model through morph relation.
     *
     * @param array|string $
     */
    public function saveSites(Model $model, array $sites): void
    {
        // Filter out null/empty values
        $sites = array_filter($sites, fn($s) => !empty($s));

        DB::table('siteables')
            ->where('siteable_id', $model->id)
            ->where('siteable_type', get_class($model))
            ->delete();

        if (empty($sites)) {
            // Default to site 1 if no sites provided
            DB::table('siteables')->insert([
                'site_id' => 1,
                'siteable_type' => get_class($model),
                'siteable_id' => $model->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            foreach ($sites as $site) {
                DB::table('siteables')->insert([
                    'site_id' => (int) $site,
                    'siteable_type' => get_class($model),
                    'siteable_id' => $model->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
