<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FilesFixture
 */
class FilesFixture extends TestFixture
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
                'lang' => 'Lorem ip',
                'filepath' => 'Lorem ipsum dolor sit amet',
                'commit_hash' => 'Lorem ipsum dolor sit amet',
                'committed_epoch_time' => 1,
                'author_name' => 'Lorem ipsum dolor sit amet',
                'author_email' => 'Lorem ipsum dolor sit amet',
                'created_epoch_time' => 1,
            ],
        ];
        parent::init();
    }
}
