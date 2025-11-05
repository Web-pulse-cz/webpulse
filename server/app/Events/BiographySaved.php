<?php

namespace App\Events;

use App\Models\Biography\Biography;
use App\Models\Career\CareerApplication;
use App\Models\Event\EventRegistration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BiographySaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected Biography $biography;

    protected array $data;
    public function __construct(Biography $biography)
    {
        $this->biography = $biography;
    }

    public function getBiography(): Biography
    {
        return $this->biography;
    }
}
