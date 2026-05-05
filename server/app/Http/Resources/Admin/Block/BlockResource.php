<?php

namespace App\Http\Resources\Admin\Block;

use App\Models\Filemanager\Filemanager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => $this->data ?? [],
            'data_preview' => $this->buildPreview($this->type, $this->data ?? []),
            'position' => $this->position,
            'is_active' => $this->is_active,
            'translations' => array_column(
                $this->translations->map(fn ($t) => [
                    'locale' => $t->locale,
                    'data' => $t->data ?? [],
                ])->toArray(),
                null,
                'locale'
            ),
            'sites' => $this->sites,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Returns `data` with image filenames resolved to a single absolute URL each
     * (smallest configured format), so admin lists/cards can show a thumbnail
     * without re-implementing the `/content/images/<type>/<path>/...` convention.
     * Translatable fields aren't resolved here since translations stay raw.
     */
    private function buildPreview(string $type, array $data): array
    {
        $fields = config("blocks.types.$type.fields", []);
        $preview = [];

        foreach ($fields as $name => $def) {
            if (($def['type'] ?? null) !== 'image' || ($def['translatable'] ?? false)) {
                continue;
            }
            $value = $data[$name] ?? null;
            if (empty($value)) {
                continue;
            }
            if ($def['multiple'] ?? false) {
                $preview[$name] = array_map(
                    fn ($filename) => $this->resolveImageUrl($type, (string) $filename),
                    is_array($value) ? array_values(array_filter($value)) : [(string) $value],
                );

                continue;
            }
            $preview[$name] = $this->resolveImageUrl($type, (string) $value);
        }

        return $preview;
    }

    private function resolveImageUrl(string $type, string $filename): string
    {
        $row = Filemanager::query()
            ->where('entity_type', $type)
            ->orderBy('position')
            ->first();

        if (! $row) {
            return url("/content/images/{$type}/{$filename}");
        }

        return url("/content/images/{$type}/{$row->path}/{$filename}");
    }
}
