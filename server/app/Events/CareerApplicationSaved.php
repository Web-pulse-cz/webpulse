<?php

namespace App\Events;

use App\Models\Career\CareerApplication;
use App\Models\Event\EventRegistration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CareerApplicationSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected CareerApplication $careerApplication;

    protected array $data;
    public function __construct(CareerApplication $careerApplication)
    {
        $this->careerApplication = $careerApplication;
    }

    public function getCareerApplication(): CareerApplication
    {
        return $this->careerApplication;
    }
}
