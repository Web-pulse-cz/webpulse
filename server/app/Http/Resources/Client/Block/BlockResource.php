<?php

namespace App\Http\Resources\Client\Block;

use App\Models\Filemanager\Filemanager;
use App\Models\Site\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale'));

        $data = array_merge(
            $this->data ?? [],
            $translation?->data ?? []
        );

        $data = $this->resolveImageFields($this->type, $data, $request);

        return [
            'id' => $this->id,
            'type' => $this->type,
            'position' => $this->position,
            'data' => $data,
        ];
    }

    /**
     * Walks the block field schema and replaces every `image` field value with a
     * structured payload — `{filename, url, formats}` — so the frontend doesn't
     * have to know the storage path convention for every block type.
     */
    private function resolveImageFields(string $type, array $data, Request $request): array
    {
        $fields = config("blocks.types.$type.fields", []);

        foreach ($fields as $name => $def) {
            if (($def['type'] ?? null) !== 'image') {
                continue;
            }
            $multiple = (bool) ($def['multiple'] ?? false);
            $value = $data[$name] ?? null;

            if ($multiple) {
                $names = is_array($value) ? array_values(array_filter($value)) : [];
                $data[$name] = array_map(
                    fn (string $filename) => $this->buildImagePayload($type, $filename, $request),
                    $names,
                );

                continue;
            }

            if (empty($value)) {
                $data[$name] = null;

                continue;
            }
            $data[$name] = $this->buildImagePayload($type, (string) $value, $request);
        }

        return $data;
    }

    /**
     * Returns `{filename, url, formats: { <format>: <absolute-url>, ... }}`. The
     * top-level `url` is the largest configured size; if no filemanager row is
     * configured for this entity type we fall back to a raw path so the value
     * still works for clients that just want a string.
     */
    private function buildImagePayload(string $type, string $filename, Request $request): array
    {
        $rows = Filemanager::query()
            ->where('entity_type', $type)
            ->when($this->resolveSiteId($request), fn ($q, $siteId) => $q->whereRelation('sites', 'sites.id', $siteId))
            ->orderBy('position')
            ->get();

        if ($rows->isEmpty()) {
            $url = url("/content/images/{$type}/{$filename}");

            return [
                'filename' => $filename,
                'url' => $url,
                'formats' => ['original' => $url],
            ];
        }

        $formats = [];
        foreach ($rows as $row) {
            $formats[$row->format] = url("/content/images/{$type}/{$row->path}/{$filename}");
        }

        return [
            'filename' => $filename,
            'url' => $formats[array_key_first($formats)],
            'formats' => $formats,
        ];
    }

    private function resolveSiteId(Request $request): ?int
    {
        $hash = $request->header('X-Site-Hash');
        if (! $hash) {
            return null;
        }

        return Site::query()->where('hash', $hash)->value('id');
    }
}
