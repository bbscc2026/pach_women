<?php

namespace App\Filament\Resources\ContentPages\Schemas;

use App\Models\ContentPage;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContentPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(80)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        RichEditor::make('content')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'link'],
                                ['h2', 'h3'],
                                ['bulletList', 'orderedList', 'blockquote'],
                                ['undo', 'redo'],
                            ])
                            ->fileAttachments(false)
                            ->extraInputAttributes(['style' => 'min-height: 24rem'])
                            ->helperText('Placeholders filled from Site settings: '.implode(' ', array_keys(ContentPage::placeholders()))),
                    ]),

                Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Section::make('Page')
                            ->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true)
                                    ->prefix('/pages/')
                                    ->helperText('The web address of the page.'),
                                Toggle::make('is_active')
                                    ->label('Visible (shown in footer)')
                                    ->default(true),
                                TextInput::make('sort_order')
                                    ->label('Footer order')
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }
}
