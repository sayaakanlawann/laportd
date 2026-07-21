<?php

namespace App\Filament\Resources\ProgramSiarans;

use App\Filament\Resources\ProgramSiarans\Pages\CreateProgramSiaran;
use App\Filament\Resources\ProgramSiarans\Pages\EditProgramSiaran;
use App\Filament\Resources\ProgramSiarans\Pages\ListProgramSiarans;
use App\Filament\Resources\ProgramSiarans\Schemas\ProgramSiaranForm;
use App\Filament\Resources\ProgramSiarans\Tables\ProgramSiaransTable;
use App\Models\ProgramSiaran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class ProgramSiaranResource extends Resource
{
    protected static ?string $model = ProgramSiaran::class;

    // --- TAMBAHAN BEDAH MIKRO: UI SIDEBAR ---
    protected static ?string $modelLabel = 'Program Siaran';
    protected static ?string $pluralModelLabel = 'Program Acara';
    protected static string | \UnitEnum | null $navigationGroup = '⚙️ Master Data';
    protected static string |\BackedEnum | null $navigationIcon = 'heroicon-o-play-circle'; // Ikon Play
    protected static ?int $navigationSort = 3;
    // --- TAMBAHAN BEDAH MIKRO: GEMBOK OTORISASI ---
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        // Hanya Admin dan Developer (Noa) yang bisa melihat dan mengakses menu ini
        return $user->role === 'admin' || $user->email === 'noa@dev.id';
    }
    // ----------------------------------------------

    protected static ?string $recordTitleAttribute = 'sidebar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('jam_tayang_default')
                    ->label('Jam Tayang Default')
                    ->placeholder('Contoh: 09:00|09:59')
                    ->helperText('Gunakan tanda garis lurus (|) sebagai pemisah jam mulai dan selesai.')
                    ->required()
                    ->maxLength(50),
                    
                Forms\Components\TextInput::make('nama_program')
                    ->label('Nama Program')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Toggle::make('is_aktif')
                    ->label('Status Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jam_tayang_default')
                    ->label('Jam Tayang')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('nama_program')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_aktif')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
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
            'index' => ListProgramSiarans::route('/'),
            'create' => CreateProgramSiaran::route('/create'),
            'edit' => EditProgramSiaran::route('/{record}/edit'),
        ];
    }
}
