<?php

namespace RyanChandler\FilamentNavigation\Filament\Resources;

use App\Models\PageBuilder;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use RyanChandler\FilamentNavigation\Models\Navigation;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class NavigationResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
        ];
    }
    protected static ?string $navigationIcon = 'heroicon-o-menu';

    protected static bool $showTimestamps = true;

    private static ?string $workNavigationLabel = null;

    private static ?string $workPluralLabel = null;

    private static ?string $workLabel = null;

    public static function disableTimestamps(bool $condition = true): void
    {
        static::$showTimestamps = ! $condition;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    TextInput::make('name')
                        ->label(__('filament-navigation::filament-navigation.attributes.name'))
                        ->reactive()
                        ->afterStateUpdated(function (?string $state, Closure $set) {
                            if (! $state) {
                                return;
                            }
                            $set('handle', Str::slug($state));
                        })
                        ->required(),
                    Toggle::make('is_subnav')
                        ->label(__('attach to page'))
                        ->onColor('success')
                        ->offColor('danger')
                        ->reactive()
                        ->afterStateUpdated(function (?string $state, Closure $set) {
                            if($state)
                                $set('page_id', null);
                        })
                        ->inline(),
                    ViewField::make('items')
                        ->label(__('filament-navigation::filament-navigation.attributes.items'))
                        ->default([])
                        ->view('filament-navigation::navigation-builder')
                        ->dehydrated()
                        ->hidden(function (Closure $get){  return $get('is_subnav');}),
                    Select::make('page_id')
                        ->searchable()
                        ->Label(__('Page'))
                        ->options(function () {
                            return PageBuilder::pluck('name','id');
                        })
                        ->hidden(function (callable $get){ return !$get('is_subnav');}),
                ])
                    ->columnSpan([
                        12,
                        'lg' => 8,
                    ]),
                Group::make([
                    Card::make([
                        TextInput::make('handle')
                            ->label(__('filament-navigation::filament-navigation.attributes.handle'))
                            ->required()
                            ->unique(column: 'handle', ignoreRecord: true),
                        View::make('filament-navigation::card-divider')
                            ->visible(static::$showTimestamps),
                        Placeholder::make('created_at')
                            ->label(__('filament-navigation::filament-navigation.attributes.created_at'))
                            ->visible(static::$showTimestamps)
                            ->content(fn (?Navigation $record) => $record ? $record->created_at->translatedFormat(config('tables.date_time_format')) : new HtmlString('&mdash;')),
                        Placeholder::make('updated_at')
                            ->label(__('filament-navigation::filament-navigation.attributes.updated_at'))
                            ->visible(static::$showTimestamps)
                            ->content(fn (?Navigation $record) => $record ? $record->updated_at->translatedFormat(config('tables.date_time_format')) : new HtmlString('&mdash;')),
                    ]),
                ])
                    ->columnSpan([
                        12,
                        'lg' => 4,
                    ]),
            ])
            ->columns(12);
    }

    public static function navigationLabel(?string $string): void
    {
        self::$workNavigationLabel = $string;
    }

    public static function pluralLabel(?string $string): void
    {
        self::$workPluralLabel = $string;
    }

    public static function label(?string $string): void
    {
        self::$workLabel = $string;
    }

    protected static function getNavigationLabel(): string
    {
        return self::$workNavigationLabel ?? parent::getNavigationLabel();
    }

    public static function getLabel(): ?string
    {
        return self::$workLabel ?? parent::getLabel();
    }

    public static function getPluralLabel(): ?string
    {
        return self::$workPluralLabel ?? parent::getPluralLabel();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-navigation::filament-navigation.attributes.name'))
                    ->searchable(),
                TextColumn::make('handle')
                    ->label(__('filament-navigation::filament-navigation.attributes.handle'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('filament-navigation::filament-navigation.attributes.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('filament-navigation::filament-navigation.attributes.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions( [
                EditAction::make(),
                DeleteAction::make(),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => NavigationResource\Pages\ListNavigations::route('/'),
            'create' => NavigationResource\Pages\CreateNavigation::route('/create'),
            'edit' => NavigationResource\Pages\EditNavigation::route('/{record}'),
        ];
    }
    public static function canCreate(): bool
    {
        return auth()->user()->can('create_navigation');
    }


    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete_navigation');
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_navigation');
    }
    public static function canView(Model $record): bool
    {
        return auth()->user()->can('view_navigation');
    }
    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update_navigation');
    }

    public static function getModel(): string
    {
        return config('filament-navigation.navigation_model') ?? Navigation::class;
    }
}
