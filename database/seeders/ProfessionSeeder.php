<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profession;
use App\Models\Skill;

class ProfessionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'ar' => 'سباك',
                'fr' => 'Plombier',
                'en' => 'Plumber',
                'value' => 'plumber',
                'skills' => [
                    ['ar'=>'تركيب وإصلاح الأنابيب','fr'=>'Installation et réparation de tuyaux','en'=>'Pipe installation & repair','value'=>'pipe_installation'],
                    ['ar'=>'تركيب سخانات المياه','fr'=>'Installation de chauffe-eau','en'=>'Water heater installation','value'=>'water_heater'],
                    ['ar'=>'تنظيف المجاري','fr'=>'Nettoyage des drains','en'=>'Drain cleaning','value'=>'drain_cleaning'],
                    ['ar'=>'كشف التسربات','fr'=>'Détection des fuites','en'=>'Leak detection','value'=>'leak_detection'],
                ],
            ],
            [
                'ar' => 'كهربائي',
                'fr' => 'Électricien',
                'en' => 'Electrician',
                'value' => 'electrician',
                'skills' => [
                    ['ar'=>'الأسلاك الكهربائية','fr'=>'Câblage électrique','en'=>'Electrical wiring & installation','value'=>'wiring_installation'],
                    ['ar'=>'تركيب وإصلاح الإضاءة','fr'=>'Installation et réparation de l\'éclairage','en'=>'Lighting installation & repair','value'=>'lighting_installation'],
                    ['ar'=>'تشخيص الدوائر','fr'=>'Dépannage de circuits','en'=>'Circuit troubleshooting','value'=>'circuit_troubleshooting'],
                    ['ar'=>'فحص السلامة','fr'=>'Inspection de sécurité','en'=>'Safety inspections','value'=>'safety_inspections'],
                ],
            ],
            [
                'ar' => 'نجار',
                'fr' => 'Menuisier',
                'en' => 'Carpenter',
                'value' => 'carpenter',
                'skills' => [
                    ['ar'=>'صناعة وإصلاح الأثاث','fr'=>'Fabrication et réparation de meubles','en'=>'Furniture making & repair','value'=>'furniture'],
                    ['ar'=>'تركيب الأبواب والنوافذ','fr'=>'Installation de portes et fenêtres','en'=>'Door/window installation','value'=>'door_window'],
                    ['ar'=>'تركيب الخزائن والرفوف','fr'=>'Construction de placards et étagères','en'=>'Cabinet & shelving construction','value'=>'cabinet_shelving'],
                    ['ar'=>'تشطيب الخشب','fr'=>'Finition du bois','en'=>'Wood finishing & restoration','value'=>'wood_finishing'],
                ],
            ],
            [
                'ar' => 'دهان / مُزيّن',
                'fr' => 'Peintre / Décorateur',
                'en' => 'Painter / Decorator',
                'value' => 'painter_decorator',
                'skills' => [
                    ['ar'=>'دهان داخلي وخارجي','fr'=>'Peinture intérieure et extérieure','en'=>'Interior & exterior painting','value'=>'interior_exterior_painting'],
                    ['ar'=>'تحضير الجدران وتشطيبها','fr'=>'Préparation et finition des murs','en'=>'Wall preparation & finishing','value'=>'wall_preparation'],
                    ['ar'=>'تركيب ورق الجدران والزخارف','fr'=>'Pose de papier peint et finitions décoratives','en'=>'Wallpaper & decorative finishes','value'=>'wallpaper_decor'],
                    ['ar'=>'استشارة الألوان','fr'=>'Conseil en couleurs','en'=>'Color consultation','value'=>'color_consultation'],
                ],
            ],
            [
                'ar' => 'بناء / عامل طوب',
                'fr' => 'Maçon / Briqueur',
                'en' => 'Mason / Bricklayer',
                'value' => 'mason_bricklayer',
                'skills' => [
                    ['ar'=>'بناء وإصلاح الجدران','fr'=>'Construction et réparation de murs','en'=>'Wall construction & repair','value'=>'wall_construction'],
                    ['ar'=>'أعمال الحجارة والأساسات الخرسانية','fr'=>'Travail de pierre et fondations en béton','en'=>'Stonework & concrete foundations','value'=>'stone_concrete'],
                    ['ar'=>'تركيب البلاط','fr'=>'Pose de carreaux','en'=>'Tile installation','value'=>'tile_installation'],
                    ['ar'=>'بناء المدافئ والمداخن','fr'=>'Construction de cheminées','en'=>'Chimney & fireplace construction','value'=>'chimney_fireplace'],
                ],
            ],
            [
                'ar' => 'عامل أسقف',
                'fr' => 'Couvreur',
                'en' => 'Roofer',
                'value' => 'roofer',
                'skills' => [
                    ['ar'=>'تركيب وإصلاح الأسقف','fr'=>'Installation et réparation de toits','en'=>'Roof installation & repair','value'=>'roof_installation'],
                    ['ar'=>'كشف وإصلاح التسربات','fr'=>'Détection et réparation des fuites','en'=>'Leak detection & sealing','value'=>'leak_detection_sealing'],
                    ['ar'=>'تركيب وصيانة المزاريب','fr'=>'Installation et entretien des gouttières','en'=>'Gutter installation & maintenance','value'=>'gutter_installation'],
                    ['ar'=>'استبدال القوباء أو البلاط','fr'=>'Remplacement des bardeaux ou tuiles','en'=>'Shingle or tile replacement','value'=>'shingle_tile_replacement'],
                ],
            ],
            [
                'ar' => 'متخصص في الأرضيات',
                'fr' => 'Spécialiste du revêtement de sol',
                'en' => 'Flooring Specialist',
                'value' => 'flooring_specialist',
                'skills' => [
                    ['ar'=>'تركيب البلاط والخشب واللامينيت والفينيل','fr'=>'Installation de carrelage, bois, stratifié et vinyle','en'=>'Tile, wood, laminate, and vinyl installation','value'=>'floor_installation'],
                    ['ar'=>'تسوية وإصلاح الأرضيات','fr'=>'Nivellement et réparation des sols','en'=>'Floor leveling & repair','value'=>'floor_leveling_repair'],
                    ['ar'=>'النعومة والتلميع','fr'=>'Ponçage et polissage','en'=>'Sanding & polishing','value'=>'sanding_polishing'],
                    ['ar'=>'تركيب السجاد','fr'=>'Pose de moquette','en'=>'Carpet installation','value'=>'carpet_installation'],
                ],
            ],
            [
                'ar' => 'فني التدفئة والتهوية وتكييف الهواء',
                'fr' => 'Technicien CVC',
                'en' => 'HVAC Technician',
                'value' => 'hvac_technician',
                'skills' => [
                    ['ar'=>'تركيب التدفئة والتهوية وتكييف الهواء','fr'=>'Installation chauffage, ventilation et climatisation','en'=>'Heating, ventilation, and AC installation','value'=>'hvac_installation'],
                    ['ar'=>'صيانة وإصلاح النظام','fr'=>'Maintenance et réparation du système','en'=>'System maintenance & repair','value'=>'system_maintenance'],
                    ['ar'=>'إعداد وضبط الترموستات','fr'=>'Configuration et calibration du thermostat','en'=>'Thermostat setup & calibration','value'=>'thermostat_setup'],
                    ['ar'=>'تنظيف وإصلاح المجاري','fr'=>'Nettoyage et réparation des conduits','en'=>'Duct cleaning & repair','value'=>'duct_cleaning'],
                ],
            ],
            [
                'ar' => 'متخصص في الأمن المنزلي',
                'fr' => 'Spécialiste de la sécurité domestique',
                'en' => 'Home Security Specialist',
                'value' => 'home_security_specialist',
                'skills' => [
                    ['ar'=>'تركيب أنظمة الإنذار','fr'=>'Installation de systèmes d\'alarme','en'=>'Alarm system installation','value'=>'alarm_installation'],
                    ['ar'=>'تركيب كاميرات المراقبة','fr'=>'Installation CCTV et surveillance','en'=>'CCTV & surveillance setup','value'=>'cctv_setup'],
                    ['ar'=>'الأقفال الذكية والتحكم في الوصول','fr'=>'Verrous intelligents et contrôle d\'accès','en'=>'Smart locks & access control','value'=>'smart_locks'],
                    ['ar'=>'الصيانة وحل المشكلات','fr'=>'Maintenance et dépannage','en'=>'Maintenance & troubleshooting','value'=>'maintenance_troubleshooting'],
                ],
            ],
        ];

        foreach ($data as $professionData) {
            $skills = $professionData['skills'];
            unset($professionData['skills']);
            $profession = Profession::create($professionData);

            foreach ($skills as $skillData) {
                $profession->skills()->create($skillData);
            }
        }
    }
}
