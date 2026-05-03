<?php

declare(strict_types=1);

use App\Models\InfringementCaseModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class InfringementCaseModelTest extends CIUnitTestCase
{
    public function testStatusLabel(): void
    {
        $this->assertSame('Detected', InfringementCaseModel::statusLabel(InfringementCaseModel::STATUS_DETECTED));
        $this->assertSame('Under Review', InfringementCaseModel::statusLabel(InfringementCaseModel::STATUS_UNDER_REVIEW));
        $this->assertSame('Resolved', InfringementCaseModel::statusLabel(InfringementCaseModel::STATUS_RESOLVED));
        $this->assertSame('Rejected', InfringementCaseModel::statusLabel(InfringementCaseModel::STATUS_REJECTED));
        $this->assertSame('custom', InfringementCaseModel::statusLabel('custom'));
    }

    public function testStatusBadgeTone(): void
    {
        $this->assertSame('success', InfringementCaseModel::statusBadgeTone(InfringementCaseModel::STATUS_RESOLVED));
        $this->assertSame('neutral', InfringementCaseModel::statusBadgeTone(InfringementCaseModel::STATUS_REJECTED));
        $this->assertSame('warning', InfringementCaseModel::statusBadgeTone(InfringementCaseModel::STATUS_DETECTED));
        $this->assertSame('neutral', InfringementCaseModel::statusBadgeTone('unknown'));
    }

    public function testPriorityLabel(): void
    {
        $this->assertSame('Critical', InfringementCaseModel::priorityLabel(InfringementCaseModel::PRIORITY_CRITICAL));
        $this->assertSame('Low', InfringementCaseModel::priorityLabel(InfringementCaseModel::PRIORITY_LOW));
        $this->assertSame('x', InfringementCaseModel::priorityLabel('x'));
    }

    public function testPriorityTone(): void
    {
        $this->assertSame('danger', InfringementCaseModel::priorityTone(InfringementCaseModel::PRIORITY_CRITICAL));
        $this->assertSame('warning', InfringementCaseModel::priorityTone(InfringementCaseModel::PRIORITY_HIGH));
        $this->assertSame('neutral', InfringementCaseModel::priorityTone(InfringementCaseModel::PRIORITY_MEDIUM));
        $this->assertSame('neutral', InfringementCaseModel::priorityTone('other'));
    }

    public function testIsTerminalAndOpenStatus(): void
    {
        $this->assertTrue(InfringementCaseModel::isTerminalStatus(InfringementCaseModel::STATUS_RESOLVED));
        $this->assertTrue(InfringementCaseModel::isTerminalStatus(InfringementCaseModel::STATUS_REJECTED));
        $this->assertFalse(InfringementCaseModel::isTerminalStatus(InfringementCaseModel::STATUS_DETECTED));

        $this->assertFalse(InfringementCaseModel::isOpenStatus(InfringementCaseModel::STATUS_RESOLVED));
        $this->assertTrue(InfringementCaseModel::isOpenStatus(InfringementCaseModel::STATUS_NEGOTIATION));
    }

    /**
     * @requires extension sqlite3
     */
    public function testUsageReportAlreadyLinkedReturnsFalseWhenIdInvalid(): void
    {
        $model = new InfringementCaseModel();
        $this->assertFalse($model->usageReportAlreadyLinked(0));
        $this->assertFalse($model->usageReportAlreadyLinked(-1));
    }
}
