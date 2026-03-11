<?php

namespace App\Jobs;

class CheckSmsDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'default';
    public int    $tries = 2;

    public function __construct(public readonly Reminder $reminder) {}

    public function handle(ReminderService $service): void
    {
        $service->refreshDeliveryStatus($this->reminder);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// ReminderService binding — AppServiceProvider
// ═══════════════════════════════════════════════════════════════════════════════

/*
 * Add to app/Providers/AppServiceProvider.php register() method:
 *
 *   use App\Services\ReminderService;
 *   use App\Services\Sms\AfricasTalkingDriver;
 *
 *   $this->app->singleton(AfricasTalkingDriver::class);
 *   $this->app->singleton(ReminderService::class);
 *
 * Register the Artisan command in app/Console/Kernel.php commands() or
 * bootstrap/app.php:
 *
 *   Commands\SmsPreviewCommand::class,
 *
 * Add SMS routes to routes/api.php inside the auth:sanctum group:
 *
 *   Route::middleware('role:superadmin,ceo,manager')->group(function () {
 *       Route::get('sms-templates', [SmsTemplateController::class, 'index']);
 *       Route::get('sms-templates/{template}', [SmsTemplateController::class, 'show']);
 *       Route::put('sms-templates/{template}', [SmsTemplateController::class, 'update']);
 *       Route::post('sms-templates/{template}/preview', [SmsTemplateController::class, 'preview']);
 *       Route::post('sms-templates/flush-cache', [SmsTemplateController::class, 'flushCache']);
 *   });
 */
