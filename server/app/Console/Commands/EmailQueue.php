<?php

namespace App\Console\Commands;

use App\Models\Email\Email;
use App\Services\EmailService;
use Illuminate\Console\Command;

class EmailQueue extends Command
{
    protected $signature = 'email:send-queue';

    protected $description = 'Send emails from the queue';

    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    public function handle()
    {
        $this->output->title('Processing email queue');

        $emails = Email::query()
            ->where('status', '!=', 'sent')
            ->where('attempts', '<', 6)
            ->orderBy('priority', 'asc')
            ->get();

        $this->output->progressStart($emails->count());
        foreach ($emails as $email) {
            try {
                $this->emailService->sendEmail($email);
                $this->output->success("Email to {$email->to} sent successfully.");
            } catch (\Exception $e) {
                $email->increment('attempts');
                $email->status = 'failed';
                $email->save();
                $this->output->error("Failed to send email to {$email->to}: {$e->getMessage()}");
            }
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }
}
