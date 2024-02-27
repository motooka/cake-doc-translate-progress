<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ScansTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ScansTable Test Case
 */
class ScansTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ScansTable
     */
    protected $Scans;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Scans',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Scans') ? [] : ['className' => ScansTable::class];
        $this->Scans = $this->getTableLocator()->get('Scans', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Scans);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ScansTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
