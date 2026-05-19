<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SiteMedia
{
    public const CACHE_KEY = 'site.display';

    public static function config(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $marqueeText = trim((string) Setting::value('header_marquee_text', ''));
            $videoType = (string) Setting::value('site_video_type', 'youtube');
            $videoUrl = trim((string) Setting::value('site_video_url', ''));
            $videoEmbed = trim((string) Setting::value('site_video_embed', ''));

            return [
                'marquee' => [
                    'enabled' => Setting::value('header_marquee_enabled', '0') === '1' && $marqueeText !== '',
                    'text' => $marqueeText,
                    'link' => trim((string) Setting::value('header_marquee_link', '')),
                ],
                'video' => [
                    'enabled' => Setting::value('site_video_enabled', '0') === '1',
                    'embed_html' => self::buildVideoEmbed($videoType, $videoUrl, $videoEmbed),
                ],
            ];
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function extractYoutubeId(string $url): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function youtubeEmbedUrl(string $videoId): string
    {
        $params = http_build_query([
            'autoplay' => 1,
            'mute' => 1,
            'loop' => 1,
            'playlist' => $videoId,
            'controls' => 1,
            'rel' => 0,
            'playsinline' => 1,
            'modestbranding' => 1,
        ]);

        return 'https://www.youtube.com/embed/'.$videoId.'?'.$params;
    }

    public static function buildVideoEmbed(string $type, string $url, string $rawEmbed): ?string
    {
        if ($type === 'iframe' && $rawEmbed !== '') {
            return self::sanitizeIframe($rawEmbed);
        }

        if ($url === '') {
            return null;
        }

        $videoId = self::extractYoutubeId($url);
        if (! $videoId) {
            return null;
        }

        $src = e(self::youtubeEmbedUrl($videoId));

        return '<iframe src="'.$src.'" title="Behna Bazar video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>';
    }

    public static function sanitizeIframe(string $html): ?string
    {
        if (! preg_match('/<iframe\b[^>]*\ssrc=["\']([^"\']+)["\'][^>]*>/i', $html, $match)) {
            return null;
        }

        $src = html_entity_decode($match[1], ENT_QUOTES);
        $videoId = self::extractYoutubeId($src);

        if ($videoId) {
            $src = self::youtubeEmbedUrl($videoId);
        } elseif (! str_starts_with($src, 'https://')) {
            return null;
        }

        $src = e($src);

        return '<iframe src="'.$src.'" title="Behna Bazar video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>';
    }
}
