<?php

namespace App\Enums;

/**
 * Homepage "How do you train?" filter — a fixed, small set of training
 * focuses (not admin-manageable, so a plain enum + JSON column on products
 * rather than a full tags table/pivot). A product can carry more than one.
 */
enum TrainingTag: string
{
    case Running = 'running';
    case Lifting = 'lifting';
    case Hiit = 'hiit';
    case Pilates = 'pilates';
    case RestDay = 'rest_day';

    public function label(): string
    {
        return match ($this) {
            self::Running => __('Running'),
            self::Lifting => __('Lifting'),
            self::Hiit => __('HIIT'),
            self::Pilates => __('Pilates'),
            self::RestDay => __('Rest day'),
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }
}
