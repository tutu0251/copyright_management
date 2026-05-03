<?php

declare(strict_types=1);

use App\Services\AuditLogService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuditLogServiceTest extends CIUnitTestCase
{
    /**
     * @param array<int|string, mixed> $args
     */
    private function invokePrivate(AuditLogService $svc, string $method, array $args = []): mixed
    {
        $ref = new \ReflectionMethod(AuditLogService::class, $method);

        return $ref->invokeArgs($svc, $args);
    }

    public function testNormalizePayloadNullAndEmptyString(): void
    {
        $svc = new AuditLogService();
        $this->assertNull($this->invokePrivate($svc, 'normalizePayload', [null]));
        $this->assertNull($this->invokePrivate($svc, 'normalizePayload', ['   ']));
    }

    public function testNormalizePayloadNonEmptyStringBecomesMessageKey(): void
    {
        $svc = new AuditLogService();
        /** @var array<string, mixed> $out */
        $out = $this->invokePrivate($svc, 'normalizePayload', [' hello ']);
        $this->assertSame('hello', $out['message']);
    }

    public function testNormalizePayloadEmptyArray(): void
    {
        $svc = new AuditLogService();
        $this->assertNull($this->invokePrivate($svc, 'normalizePayload', [[]]));
    }

    public function testNormalizePayloadStripsSensitiveKeys(): void
    {
        $svc = new AuditLogService();
        /** @var array<string, mixed> $out */
        $out = $this->invokePrivate($svc, 'normalizePayload', [[
            'name'            => 'A',
            'password'        => 'secret',
            'password_hash'   => 'x',
            'remember_token'  => 't',
        ]]);
        $this->assertSame(['name' => 'A'], $out);
    }

    public function testTruncateShortensLongStrings(): void
    {
        $svc = new AuditLogService();
        $long = str_repeat('x', 20);
        $out  = $this->invokePrivate($svc, 'truncate', [$long, 10]);
        $this->assertSame(10, strlen($out));
        $this->assertStringEndsWith('...', $out);
    }
}
