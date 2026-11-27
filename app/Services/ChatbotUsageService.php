<?php

namespace App\Services;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

class ChatbotUsageService
{
    private const RESET_TIMEZONE = 'America/Los_Angeles';

    public function record(?User $user): void
    {
        $userId = $user?->id ?? 'guest';
        $day = now(self::RESET_TIMEZONE)->toDateString();
        $minute = now('UTC')->format('Y-m-d_H:i');

        $this->bump("chatbot:usage:user:{$userId}:total");
        $this->bump("chatbot:usage:user:{$userId}:day:{$day}", now()->addDays(2));
        $this->bump("chatbot:usage:user:{$userId}:minute:{$minute}", now()->addMinutes(2));

        $this->bump('chatbot:usage:global:total');
        $this->bump("chatbot:usage:global:day:{$day}", now()->addDays(2));
        $this->bump("chatbot:usage:global:minute:{$minute}", now()->addMinutes(2));

        Cache::put("chatbot:usage:user:{$userId}:last_at", now()->toDateTimeString(), now()->addDays(2));
    }

    public function snapshot(?User $user): array
    {
        $userId = $user?->id ?? 'guest';
        $day = now(self::RESET_TIMEZONE)->toDateString();
        $minute = now('UTC')->format('Y-m-d_H:i');

        $dailyLimit = max(0, (int) config('services.gemini.daily_limit', 200));
        $minuteLimit = max(1, (int) config('services.gemini.minute_limit', 10));

        $today = (int) Cache::get("chatbot:usage:user:{$userId}:day:{$day}", 0);
        $thisMinute = (int) Cache::get("chatbot:usage:user:{$userId}:minute:{$minute}", 0);
        $total = (int) Cache::get("chatbot:usage:user:{$userId}:total", 0);
        $globalToday = (int) Cache::get("chatbot:usage:global:day:{$day}", 0);
        $globalThisMinute = (int) Cache::get("chatbot:usage:global:minute:{$minute}", 0);

        return [
            'limit_harian' => $dailyLimit,
            'limit_per_menit' => $minuteLimit,
            'request_hari_ini' => $today,
            'request_menit_ini' => $thisMinute,
            'request_total' => $total,
            'sisa_hari_ini' => $dailyLimit > 0 ? max($dailyLimit - $today, 0) : null,
            'sisa_per_menit' => max($minuteLimit - $thisMinute, 0),
            'global_hari_ini' => $globalToday,
            'global_menit_ini' => $globalThisMinute,
            'global_total' => (int) Cache::get('chatbot:usage:global:total', 0),
            'sisa_global_hari_ini' => $dailyLimit > 0 ? max($dailyLimit - $globalToday, 0) : null,
            'sisa_global_per_menit' => max($minuteLimit - $globalThisMinute, 0),
            'model' => (string) config('services.gemini.model', 'gemini-2.5-flash'),
            'terakhir_request' => Cache::get("chatbot:usage:user:{$userId}:last_at"),
            'sumber' => 'Aplikasi InvenGo (estimasi)',
            'zona_reset' => self::RESET_TIMEZONE,
        ];
    }

    private function bump(string $key, ?DateTimeInterface $expiresAt = null): void
    {
        if ($expiresAt !== null) {
            Cache::add($key, 0, $expiresAt);
        } else {
            Cache::add($key, 0);
        }

        Cache::increment($key);
    }
}
