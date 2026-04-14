<?php

namespace App\Events;

use App\Events\ProjectSavedEvent as Event;
use App\Models\Project\ProjectEvent;

class ProjectSavedListener
{
    public function handle(Event $event): void
    {
        $project = $event->getProject();
        $data = $event->getData();

        if ($project->events->count() == 0) {
            $event = new ProjectEvent;
            $event->fill([
                'name' => 'Projekt byl vytvořen',
                'description' => 'Nový projekt byl vytvořen a uložen ve stavu ',
                'project_id' => $project->id,
                'user_id' => auth()->user()->id,
                'status_id' => $project->status_id,
            ]);
            $event->project()->associate($project);
            $event->save();
        }

        if ($project->events->count() != 0 && $project->status_id !== $project->events->last()->status_id) {
            $event = new ProjectEvent;
            $event->fill([
                'name' => 'Změna stavu',
                'description' => 'Projekt byl přepnut do stavu ',
                'project_id' => $project->id,
                'user_id' => auth()->user()->id,
                'status_id' => $project->status_id,
            ]);
            $event->project()->associate($project);
            $event->save();
        }
    }
}
