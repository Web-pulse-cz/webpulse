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
        DB::table('siteables')
            ->where('siteable_id', $model->id)
            ->where('siteable_type', get_class($model))
            ->delete();

        if (empty($sites)) {
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
                    'site_id' => $site,
                    'siteable_type' => get_class($model),
                    'siteable_id' => $model->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
