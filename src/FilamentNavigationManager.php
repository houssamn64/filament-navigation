<?php

namespace RyanChandler\FilamentNavigation;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use RyanChandler\FilamentNavigation\Models\Navigation;

class FilamentNavigationManager
{
    use Macroable;

    protected array $itemTypes = [];

    public function addItemType(string $name, array | Closure $fields = []): static
    {
        $this->itemTypes[Str::slug($name)] = [
            'name' => $name,
            'fields' => $fields,
        ];

        return $this;
    }

    public function get(string $handle): ?Navigation
    {
        return static::getModel()::firstWhere('handle', $handle);
    }

    public function getItemTypes(): array
    {
        return  $this->itemTypes;
    }

    public static function getModel(): string
    {
        return config('filament-navigation.navigation_model') ?? Navigation::class;
    }
}
