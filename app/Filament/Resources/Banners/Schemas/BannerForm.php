<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('The first visible banner is shown at the top of the home page.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(80)
                            ->columnSpanFull(),
                        TextInput::make('subtitle')
                            ->maxLength(200)
                            ->columnSpanFull(),
                        FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->imageEditor()
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('1600')
                            ->imageResizeTargetHeight('2000')
                            ->imageResizeUpscale(false)
                            ->maxSize(10240)
                            ->helperText('Portrait 4:5 photo, at least 1000px wide.')
                            ->columnSpanFull(),
                        TextInput::make('button_text')
                            ->placeholder('Shop the collection'),
                        TextInput::make('link')
                            ->placeholder('/category/festive')
                            ->helperText('Leave empty to link to the shop.'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Visible')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
