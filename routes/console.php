<?php

use App\Jobs\AutoCancelExpiredRevisionsJob;
use App\Jobs\ExpireInvoicesJob;
use App\Jobs\SelfAssessmentReminderJob;
use App\Jobs\SlaMonitorJob;
use App\Jobs\SurveillanceTriggerJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new ExpireInvoicesJob)->hourly();
Schedule::job(new AutoCancelExpiredRevisionsJob)->dailyAt('00:01');
Schedule::job(new SelfAssessmentReminderJob)->dailyAt('00:30');
Schedule::job(new SlaMonitorJob)->dailyAt('01:00');
Schedule::job(new SurveillanceTriggerJob)->dailyAt('02:00');

Schedule::command('backup:clean')->dailyAt('01:30');
Schedule::command('backup:run')->dailyAt('02:30');
