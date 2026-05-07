<?php

namespace App\Filament\Resources\SelfAssessmentQuestions;

use App\Enums\UserRole;
use App\Filament\Resources\SelfAssessmentQuestions\Pages\CreateSelfAssessmentQuestion;
use App\Filament\Resources\SelfAssessmentQuestions\Pages\EditSelfAssessmentQuestion;
use App\Filament\Resources\SelfAssessmentQuestions\Pages\ListSelfAssessmentQuestions;
use App\Filament\Resources\SelfAssessmentQuestions\Schemas\SelfAssessmentQuestionForm;
use App\Filament\Resources\SelfAssessmentQuestions\Tables\SelfAssessmentQuestionsTable;
use App\Models\SelfAssessmentQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SelfAssessmentQuestionResource extends Resource
{
    protected static ?string $model = SelfAssessmentQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Self-Assessment Questions';

    protected static ?int $navigationSort = 60;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public static function form(Schema $schema): Schema
    {
        return SelfAssessmentQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SelfAssessmentQuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSelfAssessmentQuestions::route('/'),
            'create' => CreateSelfAssessmentQuestion::route('/create'),
            'edit' => EditSelfAssessmentQuestion::route('/{record}/edit'),
        ];
    }
}
