<?php

namespace App\Enums;

enum PlanningStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Borrador',
            self::PENDING => 'En Revisión',
            self::REJECTED => 'Rechazado',
            self::APPROVED => 'Aprobado',
        };
    }

    public static function tryFromLegacy(?string $value): ?self
    {
        if (empty($value)) {
            return null;
        }

        return match (strtolower($value)) {
            'borrador', 'draft' => self::DRAFT,
            'revisión', 'revision', 'pending', 'pendiente' => self::PENDING,
            'aprobado', 'approved' => self::APPROVED,
            'rechazado', 'rejected' => self::REJECTED,
            default => self::tryFrom($value),
        };
    }
}
