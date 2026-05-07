<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Application;
use App\Services\StatusTransitionService;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = PaymentStatus::PENDING->value;
        $data['original_amount'] = $data['amount'];

        return $data;
    }

    protected function afterCreate(): void
    {
        $application = Application::find($this->record->application_id);

        if ($application && $application->status === ApplicationStatus::SUBMITTED) {
            app(StatusTransitionService::class)
                ->transition($application, ApplicationStatus::INVOICED->value, auth()->user());
        }
    }
}
