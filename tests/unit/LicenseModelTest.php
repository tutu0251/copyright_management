<?php

declare(strict_types=1);

use App\Models\LicenseModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LicenseModelTest extends CIUnitTestCase
{
    public function testLicenseTypeLabelKnownTypes(): void
    {
        $this->assertSame('Exclusive', LicenseModel::licenseTypeLabel(LicenseModel::TYPE_EXCLUSIVE));
        $this->assertSame('Non-exclusive', LicenseModel::licenseTypeLabel(LicenseModel::TYPE_NON_EXCLUSIVE));
        $this->assertSame('Internal Use', LicenseModel::licenseTypeLabel(LicenseModel::TYPE_INTERNAL_USE));
    }

    public function testLicenseTypeLabelUnknownPassesThrough(): void
    {
        $this->assertSame('custom', LicenseModel::licenseTypeLabel('custom'));
    }

    public function testPaymentLabel(): void
    {
        $this->assertSame('Paid', LicenseModel::paymentLabel(LicenseModel::PAYMENT_PAID));
        $this->assertSame('Partial', LicenseModel::paymentLabel(LicenseModel::PAYMENT_PARTIAL));
        $this->assertSame('Waived', LicenseModel::paymentLabel(LicenseModel::PAYMENT_WAIVED));
        $this->assertSame('Unpaid', LicenseModel::paymentLabel(LicenseModel::PAYMENT_UNPAID));
        $this->assertSame('Unpaid', LicenseModel::paymentLabel('unknown'));
    }

    public function testStatusLabel(): void
    {
        $this->assertSame('Active', LicenseModel::statusLabel(LicenseModel::STATUS_ACTIVE));
        $this->assertSame('Expiring Soon', LicenseModel::statusLabel(LicenseModel::STATUS_EXPIRING_SOON));
        $this->assertSame('Expired', LicenseModel::statusLabel(LicenseModel::STATUS_EXPIRED));
        $this->assertSame('Cancelled', LicenseModel::statusLabel(LicenseModel::STATUS_CANCELLED));
        $this->assertSame('Draft', LicenseModel::statusLabel(LicenseModel::STATUS_DRAFT));
        $this->assertSame('Draft', LicenseModel::statusLabel('other'));
    }

    public function testEffectiveStatusDraftAndCancelledIgnoreDates(): void
    {
        $row = [
            'license_status' => LicenseModel::STATUS_DRAFT,
            'end_date'       => '2099-01-01',
        ];
        $this->assertSame(LicenseModel::STATUS_DRAFT, LicenseModel::effectiveStatus($row));

        $row['license_status'] = LicenseModel::STATUS_CANCELLED;
        $this->assertSame(LicenseModel::STATUS_CANCELLED, LicenseModel::effectiveStatus($row));
    }

    public function testEffectiveStatusPastEndDateIsExpired(): void
    {
        $row = [
            'license_status' => LicenseModel::STATUS_ACTIVE,
            'end_date'       => '2000-01-01',
        ];
        $this->assertSame(LicenseModel::STATUS_EXPIRED, LicenseModel::effectiveStatus($row));
    }

    public function testEffectiveStatusEndWithinThirtyDaysIsExpiringSoon(): void
    {
        $end = date('Y-m-d', strtotime('+10 days'));
        $row = [
            'license_status' => LicenseModel::STATUS_ACTIVE,
            'end_date'       => $end,
        ];
        $this->assertSame(LicenseModel::STATUS_EXPIRING_SOON, LicenseModel::effectiveStatus($row));
    }

    public function testEffectiveStatusFarFutureEndReturnsStoredStatusWhenActive(): void
    {
        $end = date('Y-m-d', strtotime('+90 days'));
        $row = [
            'license_status' => LicenseModel::STATUS_ACTIVE,
            'end_date'       => $end,
        ];
        $this->assertSame(LicenseModel::STATUS_ACTIVE, LicenseModel::effectiveStatus($row));
    }

    public function testEffectiveStatusStoredExpiredWithFutureEndBecomesActive(): void
    {
        $end = date('Y-m-d', strtotime('+90 days'));
        $row = [
            'license_status' => LicenseModel::STATUS_EXPIRED,
            'end_date'       => $end,
        ];
        $this->assertSame(LicenseModel::STATUS_ACTIVE, LicenseModel::effectiveStatus($row));
    }
}
