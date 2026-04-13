<?php

namespace App\Http\Resources\Admin\Project;

use App\Http\Resources\Admin\Client\ClientSimpleResource;
use App\Http\Resources\Admin\Currency\CurrencyResource;
use App\Http\Resources\Admin\TaxRate\TaxRateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'prefix' => $this->prefix,
            'description' => $this->description,
            'note' => $this->note,
            'image' => $this->image,
            'client_id' => $this->client_id,
            'client' => ClientSimpleResource::make($this->client),
            'status_id' => $this->status_id,
            'status' => ProjectStatusResource::make($this->status),
            'currency_id' => $this->currency_id,
            'currency' => CurrencyResource::make($this->currency),
            'tax_rate_id' => $this->tax_rate_id,
            'tax_rate' => TaxRateResource::make($this->taxRate),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'deadline_date' => $this->deadline_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'hourly_rate' => $this->hourly_rate,
            'expected_hours' => $this->expected_hours,
            'total_tracked_hours' => $this->total_tracked_hours,
            'expected_revenue' => $this->expected_revenue,
            'total_revenue' => $this->total_revenue,
            'total_costs' => $this->total_costs,
            'profit' => $this->profit,
            'is_archived' => $this->is_archived,
            'tags' => TagResource::collection($this->tags),
            'task_categories' => ProjectTaskCategoryResource::collection($this->taskCategories),
            'task_boards' => ProjectTaskBoardResource::collection($this->taskBoards),
            'milestones' => ProjectMilestoneResource::collection($this->milestones),
            'tasks' => ProjectTaskResource::collection($this->tasks),
            'time_entries' => ProjectTimeEntryResource::collection($this->timeEntries),
            'costs' => ProjectCostResource::collection($this->costs),
            'notes' => ProjectNoteResource::collection($this->notes),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
