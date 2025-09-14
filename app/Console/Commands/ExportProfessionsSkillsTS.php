<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Profession;

class ExportProfessionsSkillsTS extends Command
{
    protected $signature = 'export:professions-skills-ts';
    protected $description = 'Export professions and skills to a TypeScript file for frontend';

    public function handle()
    {
        $professions = Profession::get();

        $lines = [];

        // -----------------------
        // Imports & TypeDefs
        // -----------------------
        $lines[] = 'import { Lang } from "@/types/lang";';
        $lines[] = '';
        $lines[] = 'export type TranslatedLabel = {';
        $lines[] = '  en: string;';
        $lines[] = '  fr: string;';
        $lines[] = '  ar: string;';
        $lines[] = '};';
        $lines[] = '';
        $lines[] = 'export type Option = {';
        $lines[] = '  value: string;';   // ID as string
        $lines[] = '  slug: string;';    // slug for URL
        $lines[] = '  label: TranslatedLabel;';
        $lines[] = '};';
        $lines[] = '';

        // -----------------------
        // Professions
        // -----------------------
        $lines[] = '// -----------------------';
        $lines[] = '// Professions';
        $lines[] = '// -----------------------';
        $lines[] = 'export const allProfessions: Option[] = [';
        foreach ($professions as $p) {
            $lines[] = "  {";
            $lines[] = "    value: '" . $p->id . "',";
            $lines[] = "    slug: '" . addslashes($p->slug) . "',";
            $lines[] = "    label: { en: '" . addslashes($p->en) . "', fr: '" . addslashes($p->fr) . "', ar: '" . addslashes($p->ar) . "' },";
            $lines[] = "  },";
        }
        $lines[] = '];';
        $lines[] = '';


        // -----------------------
        // Helper functions
        // -----------------------
        $lines[] = '// -----------------------';
        $lines[] = '// Helper functions';
        $lines[] = '// -----------------------';
        $lines[] = 'export function getProfessions(lang: Lang) {';
        $lines[] = '  return allProfessions.map(p => ({';
        $lines[] = '    value: p.value,';
        $lines[] = '    slug: p.slug,';
        $lines[] = '    label: p.label[lang],';
        $lines[] = '  }));';
        $lines[] = '}';
        $lines[] = '';

        // -----------------------
        // Write to file
        // -----------------------
        $directory = storage_path('app/data');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $directory . '/professions-skills.ts';
        file_put_contents($filename, implode("\n", $lines));

        $this->info("TypeScript file generated: {$filename}");
    }
}
