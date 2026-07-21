<?php

namespace App\Filament\Resources\Petugas;

use App\Filament\Resources\Petugas\Pages\CreatePetugas;
use App\Filament\Resources\Petugas\Pages\EditPetugas;
use App\Filament\Resources\Petugas\Pages\ListPetugas;
use App\Filament\Resources\Petugas\Schemas\PetugasForm;
use App\Filament\Resources\Petugas\Tables\PetugasTable;
use App\Models\Petugas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
class PetugasResource extends Resource
{
    protected static ?string $model = Petugas::class;

    // --- TAMBAHAN BEDAH MIKRO: UI SIDEBAR ---
    protected static ?string $modelLabel = 'Data Petugas';
    protected static ?string $pluralModelLabel = 'Petugas & Kru';
    protected static string | \UnitEnum | null $navigationGroup = '⚙️ Master Data';
    protected static string |\BackedEnum | null $navigationIcon = 'heroicon-o-users'; // Ikon User
    protected static ?int $navigationSort = 2;
    

    protected static ?string $recordTitleAttribute = 'sidebar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Petugas')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Select::make('jabatan_utama')
                    ->label('Jabatan')
                    ->options([
                        'Technical Director' => 'Technical Director',
                        'PDU' => 'PDU',
                        'Transmisi' => 'Transmisi',
                    ])
                    ->required(),
                    
                Forms\Components\Toggle::make('is_aktif')
                    ->label('Status Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Petugas')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('jabatan_utama')
                    ->label('Jabatan')
                    ->searchable(),
                    
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
            'index' => ListPetugas::route('/'),
            'create' => CreatePetugas::route('/create'),
            'edit' => EditPetugas::route('/{record}/edit'),
        ];
    }
}
