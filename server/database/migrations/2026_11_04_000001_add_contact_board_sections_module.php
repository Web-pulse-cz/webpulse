<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $slug = 'contact_board_sections';

    private string $sourceSlug = 'contacts';

    // Contact board became a standalone module — inherit module + group permissions from `contacts`.
    public function up(): void
    {
        foreach (DB::table('sites')->get(['id', 'settings']) as $site) {
            $settings = json_decode($site->settings ?? '{}', true) ?: [];
            $enabled = $settings['enabled_modules'] ?? [];

            if (! in_array($this->sourceSlug, $enabled, true) || in_array($this->slug, $enabled, true)) {
                continue;
            }

            $settings['enabled_modules'] = array_values(array_merge($enabled, [$this->slug]));

            DB::table('sites')->where('id', $site->id)->update([
                'settings' => json_encode($settings),
            ]);
        }

        foreach (DB::table('user_groups')->get(['id', 'permissions']) as $group) {
            $permissions = json_decode($group->permissions ?? '[]', true) ?: [];
            $slugs = array_column($permissions, 'slug');

            $sourceIndex = array_search($this->sourceSlug, $slugs, true);
            if ($sourceIndex === false || in_array($this->slug, $slugs, true)) {
                continue;
            }

            $permissions[] = [
                'name' => 'Nástěnka',
                'slug' => $this->slug,
                'permissions' => $permissions[$sourceIndex]['permissions'] ?? ['view' => false, 'edit' => false, 'delete' => false],
            ];

            DB::table('user_groups')->where('id', $group->id)->update([
                'permissions' => json_encode($permissions),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('sites')->get(['id', 'settings']) as $site) {
            $settings = json_decode($site->settings ?? '{}', true) ?: [];
            $enabled = $settings['enabled_modules'] ?? [];

            if (! in_array($this->slug, $enabled, true)) {
                continue;
            }

            $settings['enabled_modules'] = array_values(array_diff($enabled, [$this->slug]));

            DB::table('sites')->where('id', $site->id)->update([
                'settings' => json_encode($settings),
            ]);
        }

        foreach (DB::table('user_groups')->get(['id', 'permissions']) as $group) {
            $permissions = json_decode($group->permissions ?? '[]', true) ?: [];
            $filtered = array_values(array_filter($permissions, fn ($p) => ($p['slug'] ?? null) !== $this->slug));

            if (count($filtered) === count($permissions)) {
                continue;
            }

            DB::table('user_groups')->where('id', $group->id)->update([
                'permissions' => json_encode($filtered),
            ]);
        }
    }
};
