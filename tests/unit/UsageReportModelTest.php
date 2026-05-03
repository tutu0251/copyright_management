<?php

declare(strict_types=1);

use App\Models\UsageReportModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class UsageReportModelTest extends CIUnitTestCase
{
    public function testDetectedTypeLabel(): void
    {
        $this->assertSame('Website', UsageReportModel::detectedTypeLabel(UsageReportModel::DETECTED_WEBSITE));
        $this->assertSame('Social Media', UsageReportModel::detectedTypeLabel(UsageReportModel::DETECTED_SOCIAL));
        $this->assertSame('foo', UsageReportModel::detectedTypeLabel('foo'));
    }

    public function testUsageTypeLabelAndBadgeTone(): void
    {
        $this->assertSame('Infringement', UsageReportModel::usageTypeLabel(UsageReportModel::USAGE_INFRINGEMENT));
        $this->assertSame('danger', UsageReportModel::usageTypeBadgeTone(UsageReportModel::USAGE_INFRINGEMENT));
        $this->assertSame('success', UsageReportModel::usageTypeBadgeTone(UsageReportModel::USAGE_AUTHORIZED));
        $this->assertSame('warning', UsageReportModel::usageTypeBadgeTone(UsageReportModel::USAGE_SUSPECTED));
        $this->assertSame('neutral', UsageReportModel::usageTypeBadgeTone('other'));
    }

    public function testDetectionMethodLabel(): void
    {
        $this->assertSame('Manual', UsageReportModel::detectionMethodLabel(UsageReportModel::METHOD_MANUAL));
        $this->assertSame('AI (coming soon)', UsageReportModel::detectionMethodLabel(UsageReportModel::METHOD_AI));
    }

    public function testIsValidDetectedSourceEmptyOrTooLong(): void
    {
        $this->assertFalse(UsageReportModel::isValidDetectedSource(''));
        $this->assertFalse(UsageReportModel::isValidDetectedSource('   '));
        $this->assertFalse(UsageReportModel::isValidDetectedSource(str_repeat('a', 513)));
    }

    public function testIsValidDetectedSourceNonUrlText(): void
    {
        $this->assertTrue(UsageReportModel::isValidDetectedSource('Some channel name'));
    }

    public function testIsValidDetectedSourceHttpUrl(): void
    {
        $this->assertTrue(UsageReportModel::isValidDetectedSource('https://example.com/path'));
        $this->assertFalse(UsageReportModel::isValidDetectedSource('https://'));
    }
}
