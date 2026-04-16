<?php

namespace App\Filament\Resources\SessionMaterials\Pages;

use App\Filament\Resources\SessionMaterials\SessionMaterialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSessionMaterials extends ManageRecords
{
    protected static string $resource = SessionMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(fn (array $data): array => SessionMaterialResource::prepareMaterialData($data)),
        ];
    }
}
