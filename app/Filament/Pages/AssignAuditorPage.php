<?php

namespace App\Filament\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\AuditAssignment;
use App\Models\User;
use App\Services\StatusTransitionService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class AssignAuditorPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $navigationLabel = 'Assign Auditor';

    protected static ?int $navigationSort = 101;

    protected string $view = 'filament.pages.assign-auditor-page';

    /** @var Collection<int, Application> */
    public Collection $applications;

    public ?string $selectedApplicationId = null;

    public ?array $assignmentData = [];

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function mount(): void
    {
        $this->loadApplications();
    }

    public function loadApplications(): void
    {
        $this->applications = Application::with('puUser.businessProfile')
            ->where('status', ApplicationStatus::AUDIT_READY->value)
            ->latest()
            ->get();
    }

    public function selectApplication(string $applicationId): void
    {
        $this->selectedApplicationId = $applicationId;
        $this->assignmentData = [];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('assignmentData.auditor_user_id')
                    ->label('Auditor')
                    ->options(
                        User::where('role', UserRole::AUDITOR->value)
                            ->where('is_active', true)
                            ->pluck('full_name', 'id')
                            ->toArray()
                    )
                    ->required()
                    ->searchable(),

                DatePicker::make('assignmentData.scheduled_date')
                    ->label('Tanggal Audit')
                    ->required()
                    ->minDate(now()->startOfDay()),

                TimePicker::make('assignmentData.scheduled_time')
                    ->label('Waktu Audit')
                    ->required(),

                TextInput::make('assignmentData.location')
                    ->label('Lokasi')
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('assignmentData');
    }

    public function assign(): void
    {
        if (! $this->selectedApplicationId) {
            Notification::make()->title('Pilih aplikasi terlebih dahulu')->danger()->send();

            return;
        }

        $data = $this->assignmentData;

        $auditorId = $data['auditor_user_id'] ?? null;
        $scheduledDate = $data['scheduled_date'] ?? null;

        if (! $auditorId || ! $scheduledDate) {
            Notification::make()->title('Lengkapi form terlebih dahulu')->danger()->send();

            return;
        }

        // Schedule conflict check
        $conflict = AuditAssignment::where('auditor_user_id', $auditorId)
            ->where('scheduled_date', $scheduledDate)
            ->whereHas('application', fn ($q) => $q->whereNotIn('status', [
                ApplicationStatus::CANCELLED->value,
                ApplicationStatus::AUTO_CANCELLED->value,
                ApplicationStatus::EXPIRED->value,
            ]))
            ->exists();

        if ($conflict) {
            Notification::make()
                ->title('Jadwal Bentrok')
                ->body('Auditor sudah memiliki jadwal pada tanggal tersebut.')
                ->danger()
                ->send();

            return;
        }

        $application = Application::findOrFail($this->selectedApplicationId);

        $assignment = AuditAssignment::create([
            'application_id' => $application->id,
            'auditor_user_id' => $auditorId,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $data['scheduled_time'] ?? null,
            'location' => $data['location'] ?? null,
            'confirmed_by_pu' => false,
        ]);

        app(StatusTransitionService::class)->transition(
            $application,
            'auditor_assigned',
            auth()->user()
        );

        // Email stubs (Phase 4 → proper Mailable)
        $puUser = $application->puUser;
        $auditor = User::find($auditorId);

        if ($puUser?->email) {
            Mail::raw(
                "Audit untuk aplikasi #{$application->id} telah dijadwalkan pada {$scheduledDate}. Lokasi: {$assignment->location}.",
                fn ($m) => $m->to($puUser->email)->subject('Jadwal Audit MFTC')
            );
        }

        if ($auditor?->email) {
            Mail::raw(
                "Anda ditugaskan untuk audit aplikasi #{$application->id} pada {$scheduledDate}. Lokasi: {$assignment->location}.",
                fn ($m) => $m->to($auditor->email)->subject('Penugasan Audit MFTC')
            );
        }

        Notification::make()
            ->title('Auditor berhasil ditugaskan')
            ->body("Auditor: {$auditor?->full_name}, Tanggal: {$scheduledDate}")
            ->success()
            ->send();

        $this->selectedApplicationId = null;
        $this->assignmentData = [];
        $this->loadApplications();
    }
}
