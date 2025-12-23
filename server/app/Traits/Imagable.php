<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait Imagable
{
    /**
     * Save image to model through morph relation.
     * @param Model $model
     * @param array|string $images
     *
     * @return void
     */
    public function saveImages(Model $model, array|string $images): void
    {
        DB::table('images')
            ->where('imagable_id', $model->id)
            ->where('imagable_type', get_class($model))
            ->delete();

        if ($images && is_array($images)) {
            foreach ($images as $image) {
                $this->insertImage($model, $image);
            }
        } else {
            $this->insertImage($model, $images);
        }
    }

    /**
     * Insert image to database to table images.
     * @param Model $model
     * @param string $image
     * @param int $key
     *
     * @return void
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
     * @return \Illuminate\Support\Collection
     */
    public function imagesAttribute($model)
    {
        return DB::table('images')
            ->where('imagable_id', $model->id)
            ->where('imagable_type', get_class($model))
            ->get();
    }
}
