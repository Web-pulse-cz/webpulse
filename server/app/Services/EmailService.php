<?php

namespace App\Services;

use App\Models\Email\Email;
use Fakturoid\FakturoidManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class EmailService
{
    /**
     * Build and save an email to the database.
     *
     * @param string $template
     * @param string $to
     * @param string $subject
     * @param array|null $cc
     * @param array|null $bcc
     * @param array $data
     * @param array|null $attachments
     * @param string|null $locale
     */
    public function buildEmail(
        string  $template,
        string  $to,
        string  $subject,
        ?array  $cc = null,
        ?array  $bcc = null,
        array   $data = [],
        ?array  $attachments = null,
        ?string $locale = null,
        ?int    $priority = 1
    ): void
    {
        DB::beginTransaction();
        try {
            $email = new Email();
            $email->fill([
                'from' => env('MAIL_FROM_ADDRESS'), //TODO: přidat z envu
                'to' => $to,
                'subject' => $subject,
                'cc' => $cc ?? [],
                'bcc' => $bcc ?? [],
                'html' => $this->loadView($template, $data, 'html', $subject),
                'plain' => $this->loadView($template, $data, 'plain', $subject),
                'attachments' => $attachments ?? [],
                'locale' => $locale ?? app()->getLocale(),
                'template' => $template,
                'priority' => $priority
            ]);
            $email->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::critical('Failed to build email', [
                'error' => $e->getMessage(),
                'to' => $to,
                'subject' => $subject,
            ]);
        }
    }

    /**
     * Send the email using Laravel's Mail facade.
     *
     * @param Email $email
     * @return void
     */
    public function sendEmail(Email $email): void
    {
        try {
            // Připrav data pro šablonu
            $data = json_decode($email->data ?? '{}', true);

            // Odeslání e-mailu pomocí Laravel Mail
            Mail::send([], $data, function ($message) use ($email) {
                $message->to($email->to)
                    ->from($email->from, 'Test Sender') // TODO: Změnit na skutečného odesílatele
                    ->subject($email->subject);

                // CC a BCC
                if (!empty($email->cc)) {
                    $message->cc($email->cc);
                }
                if (!empty($email->bcc)) {
                    $message->bcc($email->bcc);
                }

                // HTML obsah
                $message->html($email->html);

                // Plain text
                $message->text($email->html);

                // Přílohy
                if (!empty($email->attachments)) {
                    foreach ($email->attachments as $attachment) {
                        $message->attach(storage_path('app/' . $attachment));
                    }
                }
            });

            $email->status = 'sent';
            $email->sent_at = now();
            $email->attempts += 1;
            $email->save();
        } catch (\Throwable | \Exception $e) {
            Log::critical('Failed to send email', [
                'error' => $e->getMessage(),
                'email_id' => $email->id,
            ]);
            $email->status = 'failed';
            $email->attempts += 1;
            $email->save();
        }
    }


    /**     * Load the email view template.
     *
     * @param string $template
     * @param array $data
     * @param string $type
     * @return string
     */
    private function loadView(string $template, array $data, string $type, string $subject): string
    {
        $data = array_merge($data, [
            'subject' => $subject,
        ]);
        $viewPath = resource_path("views/emails/{$template}/{$type}.blade.php");
        if (!file_exists($viewPath)) {
            Log::error("Email template not found: /{$template}/{$type}.blade.php");
            return '';
        }

        return view("emails.{$template}.{$type}", $data)->render();
    }
}
