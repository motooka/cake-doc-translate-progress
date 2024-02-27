<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class InitialTables extends AbstractMigration
{
    // SQLite Data Types : https://www.sqlite.org/datatype3.html
    // SQLite AutoIncrement : https://www.sqlite.org/autoinc.html

    private $ups = [
        <<<EOSQL
CREATE TABLE scans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    branch varchar(200) NOT NULL,
    commit_hash varchar(64) NOT NULL,
    author_name varchar(200),
    author_email varchar(200),
    committed_epoch_time INTEGER NOT NULL,
    created_epoch_time INTEGER NOT NULL,
    scan_finished_epoch_time INTEGER
);
EOSQL,
        <<<EOSQL
CREATE TABLE files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lang varchar(10) NOT NULL,
    filepath varchar(255) NOT NULL,
    commit_hash varchar(64) NOT NULL,
    committed_epoch_time INTEGER NOT NULL,
    author_name varchar(200),
    author_email varchar(200),
    created_epoch_time INTEGER NOT NULL
);
EOSQL,
        <<<EOSQL
CREATE UNIQUE INDEX file_unique ON files(lang, filepath);
EOSQL,
    ];
    private $downs = [
        'DROP TABLE files;',
        'DROP TABLE scans;',
    ];
    public function up()
    {
        foreach($this->ups as $upSQL) {
            $this->execute($upSQL);
        }
    }
    public function down()
    {
        foreach($this->downs as $downSQL) {
            $this->execute($downSQL);
        }
    }
}
