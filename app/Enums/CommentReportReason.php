<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum CommentReportReason: int
{
    use ExtendsEnum;

    case Spam = 1;
    case Offensive = 2;
    case OffTopic = 3;
    case HateSpeech = 4;
    case RuleViolation = 5;

    public static function mapped(): array
    {
        return [
            'spam' => self::Spam,
            'offensive' => self::Offensive,
            'off_topic' => self::OffTopic,
            'hate_speech' => self::HateSpeech,
            'rule_violation' => self::RuleViolation,
        ];
    }

    public static function getCases(): array
    {
        $icons = [
            self::Spam->value => 'mdi-email-alert-outline',
            self::Offensive->value => 'mdi-emoticon-angry-outline',
            self::OffTopic->value => 'mdi-comment-remove-outline',
            self::HateSpeech->value => 'mdi-account-cancel-outline',
            self::RuleViolation->value => 'mdi-gavel',
        ];

        return array_map(
            fn ($item) => [
                'id' => $item->value,
                'name' => $item->getName(),
                'icon' => $icons[$item->value] ?? null,
            ],
            self::cases()
        );
    }
}

// [
//   { label: 'Спам', value: 1, icon: 'mdi-email-alert-outline' },
//   { label: 'Оскорбления', value: 2, icon: 'mdi-emoticon-angry-outline' },
//   { label: 'Не по теме', value: 3, icon: 'mdi-comment-remove-outline' },
//   { label: 'Разжигание ненависти', value: 4, icon: 'mdi-account-cancel-outline' },
//   { label: 'Нарушение правил', value: 5, icon: 'mdi-gavel' },
// ],
