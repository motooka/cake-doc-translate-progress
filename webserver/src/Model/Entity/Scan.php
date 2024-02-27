<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Scan Entity
 *
 * @property int $id
 * @property string $branch
 * @property string $commit_hash
 * @property string|null $author_name
 * @property string|null $author_email
 * @property int $committed_epoch_time
 * @property int $created_epoch_time
 * @property int|null $scan_finished_epoch_time
 */
class Scan extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'branch' => true,
        'commit_hash' => true,
        'author_name' => true,
        'author_email' => true,
        'committed_epoch_time' => true,
        'created_epoch_time' => true,
        'scan_finished_epoch_time' => true,
    ];
}
