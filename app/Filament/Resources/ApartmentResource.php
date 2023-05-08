<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApartmentResource\Pages;
use App\Filament\Resources\ApartmentResource\RelationManagers;
use App\Models\Apartment;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApartmentResource extends Resource
{
    protected static ?string $model = Apartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('apartment_type_id')
                    ->preload()
                    ->required()
                    ->searchable()
                    ->relationship('apartment_type', 'name'),
                Forms\Components\Select::make('property_id')
                    ->preload()
                    ->required()
                    ->searchable()
                    ->relationship('property', 'name'),
                Forms\Components\TextInput::make('capacity_adults')
                    ->integer()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('capacity_children')
                    ->integer()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('size')
                    ->integer()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('bathrooms')
                    ->integer()
                    ->required()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('apartment_type.name'),
                Tables\Columns\TextColumn::make('property.name'),
                Tables\Columns\TextColumn::make('size'),
                Tables\Columns\TextColumn::make('bookings_avg_rating')
                    ->label('Rating')
                    ->placeholder(0)
                    ->avg('bookings', 'rating')
                    ->formatStateUsing(fn (?string $state): ?string => number_format($state, 2)),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PropertyRelationManager::class,
            RelationManagers\RoomsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApartments::route('/'),
            'create' => Pages\CreateApartment::route('/create'),
            'edit' => Pages\EditApartment::route('/{record}/edit'),
        ];
    }
}
