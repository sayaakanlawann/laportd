<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Schema;
class CustomEditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Tambahkan Kotak Upload Foto Profil
                FileUpload::make('avatar_url')
                    ->label('Foto Profil')
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper()
                    ->directory('avatars')
                    ->disk('public')
                    ->maxSize(2048) // Maksimal 2MB
                    ->alignCenter()
                    ->imageResizeMode('cover')
    ->imageResizeTargetWidth('150')  // Resolusi dikecilkan jadi 150x150 pixel saja
    ->imageResizeTargetHeight('150'),

                // Bawaan Filament: Nama, Email, dan Password
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}