<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait Imagable
{
    /**
     * Save image to model through morph relation.
     */
    public function saveImages(Model $model, array|string|null $images): void
    {
        DB::table('images')
            ->where('imagable_id', $model->id)
            ->where('imagable_type', get_class($model))
            ->delete();

        if ($images && is_array($images)) {
            foreach ($images as $image) {
                $this->insertImage($model, $image);
            }
        } elseif ($images && $images != '' && $images != null) {
            $this->insertImage($model, $images);
        }
    }

    /**
     * Insert image to database to table images.
     */
    private function insertImage(Model $model, string $image, int $key = 0): void
    {
        DB::table('images')->insert([
            'filename' => $image,
            'name' => $image,
            'position' => $key,
            'is_main' => $key === 0,
            'imagable_type' => get_class($model),
            'imagable_id' => $model->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get model main image.
     *
     * @return string|null
     */
    public function getMainImage($model)
    {
        $image = DB::table('images')
            ->where('imagable_id', $model->id)
            ->where('imagable_type', get_class($model))
            ->where('is_main', 1)
            ->first();
        if ($image) {
            return $image->filename;
        }

        return null;
    }

    /**
     * Get model images
     *
     * @return Collection
     */
    public function imagesAttribute($model)
    {
        return DB::table('images')
            ->where('imagable_id', $model->id)
            ->where('imagable_type', get_class($model))
            ->get();
    }
}
