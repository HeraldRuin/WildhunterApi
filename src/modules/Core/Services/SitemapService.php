<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;

class SitemapService
{
    /**
     * Публичные страницы фронта, которые не лежат в базе.
     * Служебные разделы (профиль, подтверждение брони, 404) сюда не входят.
     *
     * @var list<string>
     */
    private const STATIC_PATHS = [
        '/',
        '/bases/map',
        '/blog',
        '/hunters',
        '/hunting-farms',
        '/locations',
        '/offer',
        '/personal-data',
        '/politika_konfidencialnosti',
        '/privacy',
        '/support',
        '/terms',
        '/blog/chto-takoe-zagonnaya-okhota',
        '/blog/chto-vzyat-na-okhotu-spisok',
        '/blog/kakoe-oruzhie-vybrat-novichku-dlya-okhoty',
        '/blog/kak-stat-okhotnikom-polnoe-rukovodstvo',
        '/blog/kogda-nachinaetsya-okhotnichiy-sezon-2026-goda-v-rossii',
        '/blog/kogda-zakanchivaetsya-sezon-okhoty-v-2026-godu-v-rossii-aktualnye-daty-po-regionam-i-vidam',
        '/blog/kuda-poekhat-na-okhotu',
        '/blog/ohotnichii-tury',
        '/blog/okhota-na-kosulyu-pravila-sposoby-sekrety',
        '/blog/okhota-na-losya-sposoby-sekrety-taktika',
    ];

    /**
     * @return list<array{loc: string}>
     */
    public function urls(): array
    {
        $urls = [];

        foreach (self::STATIC_PATHS as $path) {
            $urls[] = [
                'loc' => $this->absolute($path),
            ];
        }

        foreach ($this->locations() as $location) {
            $urls[] = [
                'loc' => $this->absolute('/locations/' . $location->slug),
            ];
            $urls[] = [
                'loc' => $this->absolute('/locations/' . $location->slug . '/map'),
            ];
        }

        foreach ($this->hotels() as $hotel) {
            $urls[] = [
                'loc' => $this->absolute('/hotel/' . $hotel->location_slug . '/' . $hotel->slug),
            ];
        }

        return $urls;
    }

    public function xml(): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($this->urls() as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . $this->escape($url['loc']) . '</loc>';
            $lines[] = '    <status>=200</status>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }

    private function locations(): array
    {
        return DB::table('bc_locations')
            ->where('status', 'publish')
            ->whereNull('deleted_at')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get(['slug'])
            ->all();
    }

    private function hotels(): array
    {
        return DB::table('bc_hotels')
            ->join('bc_locations', 'bc_locations.id', '=', 'bc_hotels.location_id')
            ->where('bc_hotels.status', 'publish')
            ->whereNull('bc_hotels.deleted_at')
            ->where('bc_locations.status', 'publish')
            ->whereNull('bc_locations.deleted_at')
            ->whereNotNull('bc_hotels.slug')
            ->where('bc_hotels.slug', '!=', '')
            ->whereNotNull('bc_locations.slug')
            ->where('bc_locations.slug', '!=', '')
            ->orderBy('bc_hotels.id')
            ->get([
                'bc_hotels.slug',
                'bc_locations.slug as location_slug',
            ])
            ->all();
    }

    private function absolute(string $path): string
    {
        return rtrim((string) config('app.url'), '/') . $path;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
