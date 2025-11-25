<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheInvalidationService
{
    /**
     * Clear all application caches
     */
    public static function clearAll()
    {
        self::clearEvents();
        self::clearClubs();
        self::clearUsers();
    }

    /**
     * Clear events-related caches
     */
    public static function clearEvents()
    {
        Cache::forget('events.all');
    }

    /**
     * Clear clubs-related caches
     */
    public static function clearClubs()
    {
        Cache::forget('clubs.all');
    }

    /**
     * Clear users-related caches
     */
    public static function clearUsers()
    {
        // Add user cache keys here if any are implemented
        // Cache::forget('users.all');
    }

    /**
     * Clear caches related to a specific club
     */
    public static function clearClubRelated($clubId)
    {
        self::clearClubs();
        self::clearEvents(); // Since events belong to clubs
    }

    /**
     * Clear caches related to a specific event
     */
    public static function clearEventRelated($eventId)
    {
        self::clearEvents();
    }

    /**
     * Clear caches related to a specific user
     */
    public static function clearUserRelated($userId)
    {
        self::clearClubs(); // User roles in clubs
        self::clearEvents(); // User registrations in events
    }
}