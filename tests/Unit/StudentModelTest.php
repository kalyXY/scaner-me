<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Student;
use App\Config\Database;

class StudentModelTest extends TestCase
{
    private Student $studentModel;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure test database
        Database::configure([
            'host' => $_ENV['TEST_DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['TEST_DB_PORT'] ?? '3306',
            'database' => $_ENV['TEST_DB_NAME'] ?? 'school_mvp_test',
            'username' => $_ENV['TEST_DB_USER'] ?? 'root',
            'password' => $_ENV['TEST_DB_PASS'] ?? '',
        ]);

        $this->studentModel = new Student();
    }

    public function testFindByUuid(): void
    {
        // This would require a test database setup
        $this->markTestSkipped('Requires test database setup');
        
        $uuid = '00000000-0000-0000-0000-000000000001';
        $student = $this->studentModel->findByUuid($uuid);
        
        $this->assertIsArray($student);
        $this->assertEquals($uuid, $student['uuid']);
    }

    public function testGetActiveStudents(): void
    {
        $this->markTestSkipped('Requires test database setup');
        
        $students = $this->studentModel->getActiveStudents();
        
        $this->assertIsArray($students);
        foreach ($students as $student) {
            $this->assertEquals(1, $student['is_active']);
        }
    }
}