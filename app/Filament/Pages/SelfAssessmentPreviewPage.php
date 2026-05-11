<?php

namespace App\Filament\Pages;

use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Enums\UserRole;
use App\Models\SelfAssessmentQuestion;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class SelfAssessmentPreviewPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static ?string $navigationLabel = 'SA Preview';

    protected static ?int $navigationSort = 104;

    protected string $view = 'filament.pages.self-assessment-preview-page';

    public ?string $filterScope = null;

    public ?string $filterLevel = null;

    /** @var Collection<int, SelfAssessmentQuestion> */
    public Collection $questions;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function mount(): void
    {
        $this->questions = new Collection;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('filterScope')
                    ->label('Scope')
                    ->options(collect(ScopeObject::cases())
                        ->mapWithKeys(fn (ScopeObject $s) => [$s->value => ucwords(str_replace('_', ' ', $s->value))])
                        ->toArray())
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadQuestions()),

                Select::make('filterLevel')
                    ->label('Level')
                    ->options(collect(CertificationLevel::cases())
                        ->mapWithKeys(fn (CertificationLevel $l) => [$l->value => ucwords(str_replace('_', ' ', $l->value))])
                        ->toArray())
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadQuestions()),
            ])
            ->columns(2);
    }

    public function loadQuestions(): void
    {
        $query = SelfAssessmentQuestion::where('is_active', true);

        if ($this->filterScope) {
            $query->where('scope', $this->filterScope);
        }

        if ($this->filterLevel) {
            $query->where('level', $this->filterLevel);
        }

        $this->questions = $query->orderBy('sort_order')->get();
    }
}
