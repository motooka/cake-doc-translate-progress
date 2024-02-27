<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScansFixture
 */
class ScansFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'branch' => 'Lorem ipsum dolor sit amet',
                'commit_hash' => 'Lorem ipsum dolor sit amet',
                'author_name' => 'Lorem ipsum dolor sit amet',
                'author_email' => 'Lorem ipsum dolor sit amet',
                'committed_epoch_time' => 1,
                'created_epoch_time' => 1,
                'scan_finished_epoch_time' => 1,
            ],
        ];
        parent::init();
    }
}
