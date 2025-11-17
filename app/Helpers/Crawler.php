<?php

namespace App\Helpers;

class Crawler
{
    public static function isCrawler($userAgent)
    {
        if (!$userAgent) return false;

        $crawlers = [
            'facebookexternalhit',
            'Facebot',
            'Twitterbot',
            'Slackbot',
            'TelegramBot',
            'WhatsApp',
            'Discordbot',
            'Googlebot',
            'Bingbot',
            'LinkedInBot',
        ];

        foreach ($crawlers as $crawler) {
            if (stripos($userAgent, $crawler) !== false) {
                return true;
            }
        }

        return false;
    }
}
