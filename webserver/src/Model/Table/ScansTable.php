<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Scan;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Scans Model
 *
 * @method \App\Model\Entity\Scan newEmptyEntity()
 * @method \App\Model\Entity\Scan newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Scan> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Scan get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Scan findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Scan patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Scan> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Scan|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Scan saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Scan>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Scan>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Scan>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Scan> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Scan>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Scan>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Scan>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Scan> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ScansTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('scans');
        $this->setDisplayField('branch');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('branch')
            ->maxLength('branch', 200)
            ->requirePresence('branch', 'create')
            ->notEmptyString('branch');

        $validator
            ->scalar('commit_hash')
            ->maxLength('commit_hash', 64)
            ->requirePresence('commit_hash', 'create')
            ->notEmptyString('commit_hash');

        $validator
            ->scalar('author_name')
            ->maxLength('author_name', 200)
            ->allowEmptyString('author_name');

        $validator
            ->scalar('author_email')
            ->maxLength('author_email', 200)
            ->allowEmptyString('author_email');

        $validator
            ->integer('committed_epoch_time')
            ->requirePresence('committed_epoch_time', 'create')
            ->notEmptyString('committed_epoch_time');

        $validator
            ->integer('created_epoch_time')
            ->requirePresence('created_epoch_time', 'create')
            ->notEmptyString('created_epoch_time');

        $validator
            ->integer('scan_finished_epoch_time')
            ->allowEmptyString('scan_finished_epoch_time');

        return $validator;
    }

    public function getLatestScan(): Scan | null {
        return $this->query()->orderBy([
            'id' => 'DESC',
        ])->first();
    }
}
