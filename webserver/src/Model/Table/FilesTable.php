<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Files Model
 *
 * @method \App\Model\Entity\File newEmptyEntity()
 * @method \App\Model\Entity\File newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\File> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\File get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\File findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\File patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\File> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\File|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\File saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\File>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\File>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\File>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\File> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\File>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\File>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\File>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\File> deleteManyOrFail(iterable $entities, array $options = [])
 */
class FilesTable extends Table
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

        $this->setTable('files');
        $this->setDisplayField('lang');
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
            ->scalar('lang')
            ->maxLength('lang', 10)
            ->requirePresence('lang', 'create')
            ->notEmptyString('lang');

        $validator
            ->scalar('filepath')
            ->maxLength('filepath', 255)
            ->requirePresence('filepath', 'create')
            ->notEmptyString('filepath');

        $validator
            ->scalar('commit_hash')
            ->maxLength('commit_hash', 64)
            ->requirePresence('commit_hash', 'create')
            ->notEmptyString('commit_hash');

        $validator
            ->integer('committed_epoch_time')
            ->requirePresence('committed_epoch_time', 'create')
            ->notEmptyString('committed_epoch_time');

        $validator
            ->scalar('author_name')
            ->maxLength('author_name', 200)
            ->allowEmptyString('author_name');

        $validator
            ->scalar('author_email')
            ->maxLength('author_email', 200)
            ->allowEmptyString('author_email');

        $validator
            ->integer('created_epoch_time')
            ->requirePresence('created_epoch_time', 'create')
            ->notEmptyString('created_epoch_time');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['lang', 'filepath']), ['errorField' => 'lang']);

        return $rules;
    }

    public function getTranslationStatus(string $lang): array
    {
        if(!in_array($lang, LANGUAGES, true) || $lang === 'en') {
            return [];
        }
        $sql = <<<EOSQL
WITH
    english as (
        SELECT filepath, commit_hash, committed_epoch_time
        FROM files
        WHERE lang = 'en'
    ),
    translation as (
        SELECT filepath, commit_hash, committed_epoch_time
        FROM files
        WHERE lang = :language
    )
SELECT
    coalesce(english.filepath, translation.filepath) as filepath,
    english.commit_hash,
    english.committed_epoch_time,
    translation.commit_hash,
    translation.committed_epoch_time
FROM english
    LEFT OUTER JOIN translation
        ON translation.filepath = english.filepath
UNION
SELECT
    coalesce(english.filepath, translation.filepath) as filepath,
    english.commit_hash,
    english.committed_epoch_time,
    translation.commit_hash,
    translation.committed_epoch_time
FROM translation
     LEFT OUTER JOIN english
         ON translation.filepath = english.filepath

ORDER BY filepath;
EOSQL;
        $bindings = [
            'language' => $lang,
        ];
        $conn = $this->getConnection();
        $queryResult = $conn->execute($sql, $bindings)->fetchAll('assoc');
        return $queryResult;
    }
}
