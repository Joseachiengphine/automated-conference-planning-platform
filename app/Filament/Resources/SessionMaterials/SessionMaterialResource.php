<?php

namespace App\Filament\Resources\SessionMaterials;

use App\Filament\Resources\SessionMaterials\Pages\ManageSessionMaterials;
use App\Models\SessionMaterial;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionMaterialResource extends Resource
{
    protected static ?string $model = SessionMaterial::class;
    protected static string|null|\UnitEnum $navigationGroup = 'Event Management';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocument;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('session_id')
                    ->relationship('session', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Link the file to the session where it should be available.'),
                TextInput::make('file_name')
                    ->maxLength(255)
                    ->helperText('Shown to attendees. Leave blank to use the uploaded file name.'),
                AdvancedFileUpload::make('file_path')
                    ->label('Upload Material')
                    ->required()
                    ->disk(config('filesystems.default'))
                    ->directory('session-materials')
                    ->storeFileNamesIn('original_file_name')
                    ->downloadable()
                    ->openable()
                    ->pdfPreviewHeight(400) // Customize preview height
                    ->pdfDisplayPage(1) // Set default page
                    ->pdfToolbar(true) // Enable toolbar
                    ->pdfZoomLevel(100) // Set zoom level
                    ->pdfFitType(PdfViewFit::FIT) // Set fit type
                    ->pdfNavPanes(true)
                    ->helperText('Upload the session material file. PDFs will show an inline preview automatically.'),
                DateTimePicker::make('uploaded_at')
                    ->required()
                    ->helperText('When the file became available to the team.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session.conference.title')
                    ->label('Conference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('session.title')
                    ->searchable(),
                TextColumn::make('file_name')
                    ->searchable(),
                TextColumn::make('file_path')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('file_type')
                    ->searchable(),
                TextColumn::make('uploaded_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => static::prepareMaterialData($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSessionMaterials::route('/'),
        ];
    }

    public static function prepareMaterialData(array $data): array
    {
        $originalFileName = $data['original_file_name'] ?? null;

        unset($data['original_file_name']);

        $data['file_name'] = filled($data['file_name'] ?? null)
            ? trim((string) $data['file_name'])
            : ($originalFileName ?: basename((string) ($data['file_path'] ?? '')));

        $extensionSource = (string) ($originalFileName ?: ($data['file_path'] ?? '') ?: ($data['file_name'] ?? ''));
        $data['file_type'] = strtolower((string) pathinfo($extensionSource, PATHINFO_EXTENSION));

        if (blank($data['file_type'])) {
            $data['file_type'] = 'file';
        }

        return $data;
    }
}
