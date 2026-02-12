<?php

namespace App\Events;

use App\Models\Biography\Biography;
use App\Models\Career\CareerApplication;
use App\Models\Event\EventRegistration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class BiographySaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected Biography $biography;

    protected $user;

    protected array $data;
    public function __construct(Biography $biography, Request $request)
    {
        $this->biography = $biography;
        $this->user = $request->user();
    }

    public function getBiography(): Biography
    {
        return $this->biography;
    }

    public function getUser()
    {
        return $this->user;
    }
}
