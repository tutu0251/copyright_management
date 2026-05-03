<?php

declare(strict_types=1);

use App\Models\WorkModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @requires extension sqlite3
 */
final class WorkModelTest extends CIUnitTestCase
{
    public function testFormatForViewMapsFieldsAndSplitsCreators(): void
    {
        $model = new WorkModel();
        $row = [
            'id'                => 42,
            'title'             => 'My Work',
            'work_type'         => 'book',
            'creator'           => ' Alice , Bob  ,',
            'owner'             => 'Org',
            'copyright_status'  => 'registered',
            'risk_level'        => 'High',
            'description'       => 'Desc',
            'registered_at'     => '2024-06-15',
            'updated_at'        => '2024-07-01 12:00:00',
            'license_count'     => 3,
        ];

        $view = $model->formatForView($row);

        $this->assertSame('42', $view['work_id']);
        $this->assertSame('My Work', $view['title']);
        $this->assertSame('book', $view['type']);
        $this->assertSame(['Alice', 'Bob'], $view['creators']);
        $this->assertSame(['Work #42'], $view['identifiers']);
        $this->assertSame(3, $view['license_count']);
        $this->assertStringContainsString('2024', $view['registration_date_iso']);
    }

    public function testFormatForViewHandlesMissingOptionalFields(): void
    {
        $model = new WorkModel();
        $view = $model->formatForView(['id' => 0, 'title' => 'T', 'work_type' => 'x', 'copyright_status' => 'draft', 'risk_level' => 'Low']);

        $this->assertSame('—', $view['registration_date']);
        $this->assertSame('', $view['registration_date_iso']);
        $this->assertSame([], $view['identifiers']);
    }
}
