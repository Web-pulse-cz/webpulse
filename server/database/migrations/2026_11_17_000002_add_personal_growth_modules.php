<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> slug => name */
    private array $modules = [
        'books' => 'Knihy',
        'audios' => 'Audia',
        'seminars' => 'Semináře',
    ];

    private string $sourceSlug = 'contacts';

    // Nové moduly Osobního růstu — zapnout tam, kde jsou Kontakty, a zdědit práva skupin z `contacts`.
    public function up(): void
    {
        foreach (DB::table('sites')->get(['id', 'settings']) as $site) {
            $settings = json_decode($site->settings ?? '{}', true) ?: [];
            $enabled = $settings['enabled_modules'] ?? [];

            if (! in_array($this->sourceSlug, $enabled, true)) {
                continue;
            }

            $missing = array_diff(array_keys($this->modules), $enabled);
            if (! $missing) {
                continue;
            }

            $settings['enabled_modules'] = array_values(array_merge($enabled, $missing));

            DB::table('sites')->where('id', $site->id)->update([
                'settings' => json_encode($settings),
            ]);
        }

        foreach (DB::table('user_groups')->get(['id', 'permissions']) as $group) {
            $permissions = json_decode($group->permissions ?? '[]', true) ?: [];
            $slugs = array_column($permissions, 'slug');

            $sourceIndex = array_search($this->sourceSlug, $slugs, true);
            if ($sourceIndex === false) {
                continue;
            }

            $changed = false;
            foreach ($this->modules as $slug => $name) {
                if (in_array($slug, $slugs, true)) {
                    continue;
                }
                $permissions[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'permissions' => $permissions[$sourceIndex]['permissions'] ?? ['view' => false, 'edit' => false, 'delete' => false],
                ];
                $changed = true;
            }

            if ($changed) {
                DB::table('user_groups')->where('id', $group->id)->update([
                    'permissions' => json_encode($permissions),
                ]);
            }
        }
    }

    public function down(): void
    {
        $slugs = array_keys($this->modules);

        foreach (DB::table('sites')->get(['id', 'settings']) as $site) {
            $settings = json_decode($site->settings ?? '{}', true) ?: [];
            $enabled = $settings['enabled_modules'] ?? [];
            $filtered = array_values(array_diff($enabled, $slugs));

            if (count($filtered) === count($enabled)) {
                continue;
            }

            $settings['enabled_modules'] = $filtered;
            DB::table('sites')->where('id', $site->id)->update([
                'settings' => json_encode($settings),
            ]);
        }

        foreach (DB::table('user_groups')->get(['id', 'permissions']) as $group) {
            $permissions = json_decode($group->permissions ?? '[]', true) ?: [];
            $filtered = array_values(array_filter($permissions, fn ($p) => ! in_array($p['slug'] ?? null, $slugs, true)));

            if (count($filtered) === count($permissions)) {
                continue;
            }

            DB::table('user_groups')->where('id', $group->id)->update([
                'permissions' => json_encode($filtered),
            ]);
        }
    }
};
