<?php

namespace App\Services;

class ProfanityFilterService
{
    /**
     * Daftar kata-kata kasar / toxic dalam Bahasa Indonesia & Inggris.
     * Ditulis dalam huruf kecil; pencocokan bersifat case-insensitive.
     */
    protected array $badWords = [
        // =============================================
        // BAHASA INDONESIA — Umpatan & Makian Umum
        // =============================================
        'anjing', 'anjir', 'anjrit', 'anying', 'anj1ng',
        'bangsat', 'bngst', 'b4ngsat',
        'bajingan', 'bajing4n',
        'babi', 'bab1',
        'bedebah', 'brengsek', 'brengseg',
        'celaka', 'sial',
        'kampret', 'k4mpret',
        'keparat', 'k3parat',
        'sialan', 's1alan',
        'tai', 't4i', 'tae',
        'tolol', 't0lol', 'tolo',
        'goblok', 'g0blok', 'goblook',
        'idiot', '1diot',
        'bodoh', 'b0doh',
        'dungu', 'dunggu',
        'bebal', 'pea',
        'sinting', 's1nting',
        'gila', 'g1la',
        'edan', '3dan',
        'asu', '4su',
        'ndasmu', 'ndas',
        'matamu',
        'pukimak', 'puki',
        'kontol', 'k0ntol',
        'memek', 'm3mek', 'mmg', 'memeg',
        'ngentot', 'ng3ntot', 'ngentod',
        'jancok', 'j4ncok', 'jancuk', 'j4ncuk', 'dancuk',
        'cuk', 'cok',
        'jancik',
        'laknat',
        'buset', 'buse',
        'perek', 'sundal', 'sundel',
        'pelacur', 'lonte', 'l0nte',
        'jalang', 'j4lang',
        'cabul',
        'mampus', 'm4mpus',
        'monyet', 'm0nyet',
        'pengecut',
        'sontoloyo', 's0ntoloyo',
        'geblek', 'g3blek',
        'pekok', 'p3kok',
        'ndableg',
        'koplak', 'k0plak',
        'kampungan',
        'bangke', 'bangkai', 'b4ngke',
        'kurangajar',
        'bejat', 'amoral',
        'nistamu',
        'jahanam', 'jah4nam',
        'terkutuk', 't3rkutuk',
        'fasik',
        'rendahan', 'sampah', 's4mpah',
        'parasit', 'benalu',
        'mesum', 'm3sum',
        'najis', 'n4jis',
        'b3jat',

        // =============================================
        // BAHASA INGGRIS — Swear Words & Slurs
        // =============================================
        'fuck', 'fck', 'fucc', 'fuk', 'phuck',
        'shit', 'crap',
        'bitch', 'biatch',
        'asshole', 'bastard',
        'cunt',
        'damn', 'damnit',
        'dick',
        'cock',
        'pussy',
        'nigger', 'nig', 'nigga',
        'whore',
        'slut',
        'motherfucker',
        'faggot', 'fag',
        'retard',
        'moron',
        'imbecile',
        'dumbass',
        'prick',
        'wanker',
        'jackass',
        'dipshit',
        'horseshit',
        'bullshit',
        'scum', 'scumbag',
        'pervert', 'perv',
        'creep',
        'pedophile', 'pedo',
        'rapist',
        'kys',
        'stupid',
        'pathetic',
        'worthless',
        'useless',
        'shutup',
        'hateyou',
        'dropdead',
    ];

    /**
     * Filter teks: ganti kata kasar dengan bintang (*) sebanyak panjang kata.
     */
    public function filter(string $text): string
    {
        foreach ($this->badWords as $word) {
            $escaped = preg_quote($word, '/');
            $pattern = '/\b' . $escaped . '\b/ui';
            $text = preg_replace_callback($pattern, function (array $matches): string {
                return str_repeat('*', mb_strlen($matches[0]));
            }, $text) ?? $text;
        }

        return $text;
    }

    /**
     * Cek apakah teks mengandung kata kasar.
     */
    public function containsProfanity(string $text): bool
    {
        foreach ($this->badWords as $word) {
            $escaped = preg_quote($word, '/');
            $pattern = '/\b' . $escaped . '\b/ui';
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter array kolom-kolom tertentu dari sebuah array data.
     *
     * @param  array  $data    Data asli
     * @param  array  $fields  Nama kolom yang akan difilter
     * @return array           Data dengan kolom yang sudah difilter
     */
    public function filterFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = $this->filter($data[$field]);
            }
        }

        return $data;
    }
}
