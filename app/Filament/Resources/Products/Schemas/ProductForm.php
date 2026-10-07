<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Livewire\Shop;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Section::make('Details')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(150)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->columnSpanFull(),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                TextInput::make('sku')
                                    ->label('SKU / code')
                                    ->unique(ignoreRecord: true),
                                Textarea::make('description')
                                    ->rows(5)
                                    ->columnSpanFull(),
                                TextInput::make('fabric')
                                    ->placeholder('e.g. Rayon, Cotton, Georgette'),
                                TextInput::make('color')
                                    ->label('Colour'),
                            ]),

                        Section::make('Images')
                            ->description('The first image is the main photo. Drag to reorder. Portrait 3:4 photos look best.')
                            ->schema([
                                FileUpload::make('images')
                                    ->hiddenLabel()
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->appendFiles()
                                    ->panelLayout('grid')
                                    ->maxFiles(8)
                                    // Phone camera photos are shrunk in the browser before upload.
                                    ->imageResizeMode('contain')
                                    ->imageResizeTargetWidth('1600')
                                    ->imageResizeTargetHeight('2133')
                                    ->imageResizeUpscale(false)
                                    ->maxSize(10240)
                                    ->disk('public')
                                    ->directory('products')
                                    ->imageEditor(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Section::make('Visibility')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Visible on website')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Featured'),
                                Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Section::make('Price & stock')
                            ->schema([
                                TextInput::make('price')
                                    ->label('MRP')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->prefix('₹')
                                    ->live(onBlur: true),
                                TextInput::make('sale_price')
                                    ->label('Sale price')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->helperText('Leave empty when not on sale.')
                                    ->lt('price')
                                    ->validationMessages(['lt' => 'Sale price must be lower than the MRP.'])
                                    ->hint(fn (Get $get) => filled($get('sale_price')) && (float) $get('price') > 0
                                        ? round(100 - ((float) $get('sale_price') / (float) $get('price')) * 100).'% off'
                                        : null)
                                    ->live(onBlur: true),
                                TextInput::make('stock')
                                    ->label('Stock (pieces)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(1),
                            ]),

                        Section::make('Sizes')
                            ->schema([
                                CheckboxList::make('sizes')
                                    ->hiddenLabel()
                                    ->options(array_combine(Shop::SIZES, Shop::SIZES))
                                    ->columns(3)
                                    ->helperText('Leave all unticked if size does not apply (e.g. shawls).'),
                            ]),
                    ]),
            ]);
    }
}
