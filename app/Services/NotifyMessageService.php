<?php

namespace App\Services;

class NotifyMessageService
{
    const ACTOR_MAX_LENGTH = 30;

    public static function get($type, $actor)
    {
        if (empty($actor)) {
            return 'You have a new notification';
        }

        if (strpos(trim($actor), ' ') !== false) {
            return 'You have a new notification';
        }

        if (strlen($actor) > self::ACTOR_MAX_LENGTH) {
            $actor = substr($actor, 0, (self::ACTOR_MAX_LENGTH - 3)).'..';
        }

        if (! str_starts_with($actor, '@')) {
            $actor = '@'.$actor;
        }

        switch ($type) {
            case 'follow':
                return self::isFollow($actor);
                break;

            case 'like':
                return self::isLike($actor);
                break;

            case 'comment':
                return self::isComment($actor);
                break;

            case 'mention':
                return self::isMention($actor);
                break;

            case 'share':
                return self::isShare($actor);
                break;

            default:
                return 'You have a new notification';
                break;
        }
    }

    public static function isFollow($actor)
    {
        return "$actor started following you";
    }

    public static function isLike($actor)
    {
        return "$actor liked your post";
    }

    public static function isComment($actor)
    {
        return "$actor commented on your post";
    }

    public static function isMention($actor)
    {
        return "$actor mentioned you";
    }

    public static function isShare($actor)
    {
        return "$actor shared your post";
    }
}
