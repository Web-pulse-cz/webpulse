<?php

namespace App\Models\PersonalGrowth;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPersonalGrowthTasks
{
    public function tasks(): MorphMany
    {
        return $this->morphMany(PersonalGrowthTask::class, 'taskable')->orderBy('position')->orderBy('id');
    }

    public function doneTasks(): MorphMany
    {
        return $this->morphMany(PersonalGrowthTask::class, 'taskable')->where('is_done', true);
    }

    /**
     * Sync úkolů z formuláře: existující (id) aktualizuje, nové vytvoří, chybějící smaže.
     *
     * @param  array<int, array{id?: int|null, title?: string, is_done?: bool}>  $tasks
     */
    public function syncTasks(array $tasks): void
    {
        $keepIds = [];

        foreach (array_values($tasks) as $position => $data) {
            $title = trim((string) ($data['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $task = ! empty($data['id'])
                ? $this->tasks()->whereKey($data['id'])->first()
                : null;
            $task ??= $this->tasks()->make();

            $isDone = (bool) ($data['is_done'] ?? false);
            $task->title = $title;
            $task->position = $position;
            if ($isDone !== (bool) $task->is_done || ! $task->exists) {
                $task->done_at = $isDone ? now() : null;
            }
            $task->is_done = $isDone;
            $task->save();

            $keepIds[] = $task->id;
        }

        $this->tasks()->whereNotIn('id', $keepIds)->delete();
    }

    protected static function bootHasPersonalGrowthTasks(): void
    {
        static::deleting(fn ($model) => $model->tasks()->delete());
    }
}
