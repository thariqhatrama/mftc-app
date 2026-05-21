<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Invoice;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('application_id')
                                    ->label('Application')
                                    ->options(function (?Invoice $record): array {
                                        return Application::query()
                                            ->where(function ($query) use ($record): void {
                                                $query->where('status', ApplicationStatus::SUBMITTED)
                                                    ->whereDoesntHave('invoice');

                                                if ($record) {
                                                    $query->orWhere('id', $record->application_id);
                                                }
                                            })
                                            ->get()
                                            ->mapWithKeys(fn (Application $app) => [
                                                $app->id => $app->id.' — '.($app->puUser?->businessProfile?->company_name ?? 'N/A'),
                                            ])
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->disabledOn('edit'),
                                TextInput::make('invoice_number')
                                    ->required()
                                    ->default(fn (): string => self::generateInvoiceNumber())
                                    ->unique(ignoreRecord: true)
                                    ->disabledOn('edit'),
                                TextInput::make('amount')
                                    ->label('Amount (Rp)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                                Textarea::make('description')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV/MFTC/{$year}/%")
            ->orderByDesc('invoice_number')
            ->first();

        $seq = 1;

        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->invoice_number);
            $seq = ((int) end($parts)) + 1;
        }

        return sprintf('INV/MFTC/%d/%04d', $year, $seq);
    }
}
