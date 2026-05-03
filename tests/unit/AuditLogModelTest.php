<?php

declare(strict_types=1);

use App\Models\AuditLogModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @requires extension sqlite3
 */
final class AuditLogModelTest extends CIUnitTestCase
{
    public function testListForEntityReturnsEmptyWhenEntityIdInvalid(): void
    {
        $model = new AuditLogModel();
        $this->assertSame([], $model->listForEntity('work', 0));
        $this->assertSame([], $model->listForEntity('work', -3));
    }

    public function testListForEntityReturnsEmptyWhenEntityTypeEmpty(): void
    {
        $model = new AuditLogModel();
        $this->assertSame([], $model->listForEntity('', 1));
    }
}
