<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@mangaku.test',
            'password' => Hash::make('password'),
        ]);

        $samples = [
            [
                'title' => 'Solo leveling Petualangan',
                'author' => 'Chugong',
                'status' => 'ongoing',
                'synopsis' => "Seorang hunter terlemah mendapat kekuatan misterius untuk naik level sendirian.\nDunia berubah, dan dia harus bertahan di antara gerbang monster.",
            ],
            [
                'title' => 'Naruto Reborn',
                'author' => 'Masashi Kishimoto',
                'status' => 'completed',
                'synopsis' => 'Perjalanan seorang ninja muda dari desa terpinggirkan menuju puncak dunia shinobi.',
            ],
            [
                'title' => 'One Piece Legenda Laut',
                'author' => 'Eiichiro Oda',
                'status' => 'ongoing',
                'synopsis' => 'Kelompok bajak laut pencari harta karun legendaris One Piece melintasi Grand Line.',
            ],
            [
                'title' => 'Attack on Titan: Wings of Freedom',
                'author' => 'Hajime Isayama',
                'status' => 'completed',
                'synopsis' => 'Kemanusiaan hidup di balik tembok raksasa untuk melawan Titan yang memangsa manusia.',
            ],
            [
                'title' => 'Jujutsu Kaisen Sorcery Battle',
                'author' => 'Gege Akutami',
                'status' => 'ongoing',
                'synopsis' => 'Siswa SMA menelan jari kutukan dan terjebak dalam dunia kutukan serta sihir jujutsu.',
            ],
        ];

        foreach ($samples as $index => $data) {
            $manga = Manga::create($data);

            $cover = $this->coverSvg($data['title'], $index);
            $coverPath = 'covers/'.$manga->slug.'.svg';
            Storage::disk('public')->put($coverPath, $cover);
            $manga->update(['cover' => $coverPath]);

            $chapterCount = 3;
            for ($n = 1; $n <= $chapterCount; $n++) {
                $chapter = Chapter::create([
                    'manga_id' => $manga->id,
                    'number' => $n,
                    'title' => 'Chapter '.$n,
                    'published_at' => now()->subDays($chapterCount - $n),
                ]);

                for ($p = 1; $p <= 4; $p++) {
                    $path = 'pages/'.$manga->slug.'/chapter-'.$chapter->id.'/sample-'.$p.'.svg';
                    Storage::disk('public')->put($path, $this->pageSvg($data['title'], $n, $p));
                    $chapter->pages()->create([
                        'image_path' => $path,
                        'position' => $p,
                    ]);
                }
            }
        }
    }

    private function coverSvg(string $title, int $index): string
    {
        $colors = ['#312e81', '#1e3a8a', '#3b0764', '#064e3b', '#7c2d12'];
        $bg = $colors[$index % count($colors)];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="420" viewBox="0 0 300 420">
  <rect width="300" height="420" fill="{$bg}"/>
  <rect x="12" y="12" width="276" height="396" fill="none" stroke="#ffffff33" stroke-width="2"/>
  <text x="150" y="200" text-anchor="middle" fill="#e5e7eb" font-family="sans-serif" font-size="20" font-weight="bold">{$this->esc($title)}</text>
  <text x="150" y="240" text-anchor="middle" fill="#9ca3af" font-family="sans-serif" font-size="13">Sampel Cover</text>
</svg>
SVG;
    }

    private function pageSvg(string $title, int $chapter, int $page): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1100" viewBox="0 0 800 1100">
  <rect width="800" height="1100" fill="#111827"/>
  <rect x="20" y="20" width="760" height="1060" fill="none" stroke="#374151" stroke-width="2"/>
  <text x="400" y="520" text-anchor="middle" fill="#e5e7eb" font-family="sans-serif" font-size="28" font-weight="bold">{$this->esc($title)}</text>
  <text x="400" y="570" text-anchor="middle" fill="#9ca3af" font-family="sans-serif" font-size="20">Chapter {$chapter} — Halaman {$page}</text>
</svg>
SVG;
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
