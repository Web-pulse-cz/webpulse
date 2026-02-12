<?php

namespace App\Events;

use App\Models\Demand\Demand;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DemandSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected Demand $demand;

    protected array $data;
    public function __construct(Demand $demand)
    {
        $this->demand = $demand;
    }

    public function getDemand(): Demand
    {
        return $this->demand;
    }
}
