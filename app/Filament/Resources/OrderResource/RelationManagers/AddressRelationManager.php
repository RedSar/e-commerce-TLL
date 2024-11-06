<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'address';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personne à contacter')->schema(
                    [ Forms\Components\TextInput::make('first_name')->label('Prénom')->required()->maxLength(255),
                    Forms\Components\TextInput::make('last_name')->label('Nom')->required()->maxLength(255),
                    Forms\Components\TextInput::make('phone')->tel()->label('N° de téléphone')->required()->maxLength(20),
                   ]
                )->columns(3),
                Forms\Components\Section::make('Informations sur l\'adresse')->schema([
                    Forms\Components\Textarea::make('street_address')
                       ->required()->columnSpan(3),
                      
                   Forms\Components\TextInput::make('city')
                       ->required()
                       ->maxLength(255),
                   Forms\Components\TextInput::make('state')
                       ->required()
                       ->maxLength(255),
                   Forms\Components\TextInput::make('zip_code')
                       ->required()
                       ->maxLength(255),
                ])->columns(3)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street_address')
            ->columns([
               

                Tables\Columns\TextColumn::make('fullname')->label('Nom'),
                Tables\Columns\TextColumn::make('phone')->label('N° de téléphone'),
                Tables\Columns\TextColumn::make('street_address')->label('Addresse'),
                Tables\Columns\TextColumn::make('city')->label('Ville'),
                Tables\Columns\TextColumn::make('state')->label('Pays'),
                Tables\Columns\TextColumn::make('zip_code')->label('Code Postal'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\ActionGroup::make(
                    [Actions\ViewAction::make(), Actions\EditAction::make(), Actions\DeleteAction::make()]
                )
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->paginated(false);
    }

   
}
