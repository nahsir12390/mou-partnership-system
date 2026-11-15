<?php

use App\Models\Agreement;
use Carbon\CarbonImmutable;

test('expiry day remains valid through the institutional calendar day', function () {
    config()->set('institution.timezone', 'Africa/Lagos');
    $agreement = new Agreement(['expiry_date' => '2026-11-15']);

    expect($agreement->daysUntilExpiry(CarbonImmutable::parse('2026-11-15 23:59:59', 'Africa/Lagos')))->toBe(0)
        ->and($agreement->daysUntilExpiry(CarbonImmutable::parse('2026-11-16 00:00:00', 'Africa/Lagos')))->toBe(-1);
});

test('expiry date calculation uses signed calendar days rather than an absolute timestamp', function () {
    $agreement = new Agreement(['expiry_date' => '2026-11-16']);

    expect($agreement->daysUntilExpiry(CarbonImmutable::parse('2026-11-15 23:59:59', 'Africa/Lagos')))->toBe(1);
});
