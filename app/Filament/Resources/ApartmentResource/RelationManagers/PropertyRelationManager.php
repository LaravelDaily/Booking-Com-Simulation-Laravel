<?php

namespace App\Filament\Resources\ApartmentResource\RelationManagers;

use Filament\Forms;
use App\Rules\LatitudeRule;
use Filament\Resources\Form;
use App\Rules\LongitudeRule;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PropertyRelationManager extends RelationManager
{
    protected static string $relationship = 'property';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('address_street')
                    ->required(),
                Forms\Components\TextInput::make('address_postcode')
                    ->required(),
                Forms\Components\Select::make('city_id')
                    ->relationship('city', 'name')
                    ->preload()
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('lat')
                    ->required()
                    ->rules([new LatitudeRule()]),
                Forms\Components\TextInput::make('long')
                    ->required()
                    ->rules([new LongitudeRule()]),
                Forms\Components\Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
                    ->image()
                    ->maxSize(5000)
                    ->multiple()
                    ->columnSpanFull()
                    ->collection('avatars')
                    ->conversion('thumbnail'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('owner.name'),
                Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('city.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
