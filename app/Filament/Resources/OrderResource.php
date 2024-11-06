<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationLabel(): string
    {
        return __('dashboard.orders') ?? static::getTitleCasePluralModelLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('ORDER INFORMATION')->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label("Name of User")
                        ->preload()
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('payment_method')
                        ->label("Select Payment Method")
                        ->required()
                        ->options([
                            'stripe' => 'Stripe',
                            'cod' => 'Cash On Delivery',
                        ]),
                    Forms\Components\Select::make('payment_status')
                        ->label("Select Payment Status")
                        ->required()
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed'
                        ])->default('pending'),

                    Forms\Components\ToggleButtons::make('status')
                        ->label("Select Status")
                        ->inline()
                        ->default('new')
                        ->options([
                            'new' => 'New',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                        ])
                        ->colors([
                            'new' => 'primary',
                            'processing' => 'info',
                            'shipped' => 'success',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                        ])
                        ->icons([
                            'new' => 'heroicon-m-sparkles',
                            'processing' => 'heroicon-m-arrow-path',
                            'shipped' => 'heroicon-m-truck',
                            'delivered' => 'heroicon-m-check-badge',
                            'cancelled' => 'heroicon-m-x-circle',
                        ]),

                    Forms\Components\Select::make('currency')
                        ->required()
                        ->options([
                            'MAD' => 'Moroccan Dirham (DH)',
                            'USD' => 'US Dollar ($)',
                            'EUR' => 'Euro (€)',
                            'GBP' => 'Pound Sterling (£)',
                        ])
                        ->default('MAD')
                        ->label(__('resources.orders.fields.currency')),

                    Forms\Components\Select::make('shipping_method')
                        ->required()
                        ->options([
                            'fedex' => 'Fedex',
                            'dhl' => 'DHL',
                            'usps' => 'USPS',
                            'ups' => 'UPS',
                        ])
                        ->default('fedex')
                        ->label(__('resources.orders.fields.shipping_method')),

                    Forms\Components\Textarea::make('notes')
                        ->label(__('resources.orders.fields.notes'))
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('ORDER ITEMS')->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->columns(12)
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->relationship('product', 'name')
                                ->label("Name of Product")
                                ->preload()
                                ->required()
                                ->searchable()
                                ->distinct()
                                ->reactive()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $price = Product::find($state)?->price ?? 0;
                                    $set('unit_amount', $price);
                                    $set('total_amount', $price * $get('quantity'));
                                })
                                ->columnSpan(6),


                            Forms\Components\TextInput::make('quantity')
                                ->label("Quantity")
                                ->reactive()
                                ->afterStateUpdated(fn($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount')))
                                ->required()
                                ->default(1)
                                ->minValue(1)
                                ->numeric(),

                            Forms\Components\TextInput::make('unit_amount')
                                ->label("Unit Amount")
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('total_amount')
                                ->label("Total Amount")
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(3)
                                ->required(),
                        ]),

                    Forms\Components\Placeholder::make("")->content(new HtmlString('<hr>')),

                    Forms\Components\Placeholder::make('Total Amount')
                        ->content(function (Set $set, Get $get) {
                            $total = 0;
                            $items = $get('items');
                            if (!$items) {
                                return 0;
                            } else {
                                foreach ($items as $item) {
                                    $total += $item['total_amount'];
                                }
                                $set('grand_total', $total);

                                return Number::currency(number: $total, in: $get('currency'), locale: 'fr');
                            }
                        }),

                    Forms\Components\Hidden::make('grand_total')->default(0)
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label("Name of User"),
                Tables\Columns\TextColumn::make('currency')->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label("Total Amount")
                  
                    ->money(fn($record) => $record->currency)
                    ->alignEnd(),

                
                Tables\Columns\SelectColumn::make('status')
                ->label("Status")
    
                ->options([
                    'new' => 'New',
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                ])
                ->default(fn($record)=>$record->statuc) ->alignCenter(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Méthode de paiement')
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('shipping_method')
                    ->label('Méthode de livraison')
                    ->searchable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->colors([
                        'info' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                     
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
               
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label("Par status")
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Par la méthode de paiement')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed'
                    ])
                    
                   
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
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
            RelationManagers\AddressRelationManager::class
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return  'pending : '.static::getModel()::where('payment_status', 'pending')->count();
    
        
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
