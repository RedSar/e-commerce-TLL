<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $modelLabel = "Produits";
    
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make(['PRODUCT INFORMATION'])->schema([
                    Forms\Components\Section::make('PRODUCT INFORMATIONS')->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', str($state)->slug('-')) : null)
                            ->live(onBlur: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->dehydrated()
                            ->unique(Product::class, ignoreRecord: true)
                            ->disabled(),
                        Forms\Components\MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->maxHeight('234px')
                            ->fileAttachmentsDirectory('products'),
                    ])->columns(2),
                    Forms\Components\Section::make('PRODUCT IMAGES')->schema([
                        FileUpload::make('images')
                        ->label('Upload images')
                            ->multiple()
                            ->image()
                            ->directory('products')
                            ->maxFiles(5)
                            ->reorderable(),
                    ])
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make('PRODUCT PRICE')->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->suffix('DH')
                            ->required()
                    ]),

                    Section::make('BRANDS AND CATEGORIES')->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('brand_id')
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name')
                            ->required(),
                    ]),
                    Section::make('PRODUCT STATUS')->schema([
                        Forms\Components\Toggle::make('in_stock')->default(true)->required(),
                        Forms\Components\Toggle::make('is_active')->default(true)->required(),
                        Forms\Components\Toggle::make('is_featured')->default(true)->required(),
                        Forms\Components\Toggle::make('on_sale')->default(true)->required(),
                    ])
                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Category.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Brand.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                   ->formatStateUsing(function ($state) {
                        // Format the number with a space as the thousands separator and 2 decimal places
                        return number_format($state, 2, '.', ' ') . ' DH';
                    })
                  ->sortable(),
                
                Tables\Columns\IconColumn::make('in_stock')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),


                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('on_sale')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
               
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('dashboard.by-category'))
                    ->relationship('category', 'name'),
                    Tables\Filters\SelectFilter::make('brand')
                    ->label(__('dashboard.by-brand'))
                    ->relationship('brand', 'name')
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
