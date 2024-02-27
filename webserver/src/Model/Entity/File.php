<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * File Entity
 *
 * @property int $id
 * @property string $lang
 * @property string $filepath
 * @property string $commit_hash
 * @property int $committed_epoch_time
 * @property string|null $author_name
 * @property string|null $author_email
 * @property int $created_epoch_time
 */
class File extends Entity
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
        'lang' => true,
        'filepath' => true,
        'commit_hash' => true,
        'committed_epoch_time' => true,
        'author_name' => true,
        'author_email' => true,
        'created_epoch_time' => true,
    ];
}
