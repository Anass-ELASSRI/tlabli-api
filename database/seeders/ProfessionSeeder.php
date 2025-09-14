<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profession;

class ProfessionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'ar' => 'سباك',
                'fr' => 'Plombier',
                'en' => 'Plumber',
                'slug' => 'plumber',
            ],
            [
                'ar' => 'كهربائي',
                'fr' => 'Électricien',
                'en' => 'Electrician',
                'slug' => 'electrician',
            ],
            [
                'ar' => 'نجار',
                'fr' => 'Menuisier',
                'en' => 'Carpenter',
                'slug' => 'carpenter',
            ],
            [
                'ar' => 'دهان / مُزيّن',
                'fr' => 'Peintre / Décorateur',
                'en' => 'Painter / Decorator',
                'slug' => 'painter_decorator',
            ],
            [
                'ar' => 'بناء / عامل طوب',
                'fr' => 'Maçon / Briqueur',
                'en' => 'Mason / Bricklayer',
                'slug' => 'mason_bricklayer',
            ],
            [
                'ar' => 'عامل أسقف',
                'fr' => 'Couvreur',
                'en' => 'Roofer',
                'slug' => 'roofer',
            ],
            [
                'ar' => 'متخصص في الأرضيات',
                'fr' => 'Spécialiste du revêtement de sol',
                'en' => 'Flooring Specialist',
                'slug' => 'flooring_specialist',
            ],
            [
                'ar' => 'فني التدفئة والتهوية وتكييف الهواء',
                'fr' => 'Technicien CVC',
                'en' => 'HVAC Technician',
                'slug' => 'hvac_technician',
            ],
            [
                'ar' => 'متخصص في الأمن المنزلي',
                'fr' => 'Spécialiste de la sécurité domestique',
                'en' => 'Home Security Specialist',
                'slug' => 'home_security_specialist',
            ],
        ];

        foreach ($data as $professionData) {
            Profession::create($professionData);
        }
    }
}
