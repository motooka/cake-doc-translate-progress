<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\Entity\File;
use App\Model\Table\FilesTable;
use Cake\ORM\TableRegistry;

class GitRepo
{
    const REPO_DIR_PARENT = TMP;
    const REPO_DIR = self::REPO_DIR_PARENT . 'repo';
    const REPO_GIT_URL = 'https://github.com/cakephp/docs.git';

    public static function clone() {
        // TODO: implement
    }

    public static function pull() {
        // TODO: implement
    }

    public static function checkoutBranch(string $branch) {
        // TODO: implement
    }

    private static function _gitLog(string $filePath): array {
        // TODO: execute "git log -1" command
        return [
            'commit_hash' => '', // TODO
            'committed_epoch_time' => '', // TODO
            'author_name' => '', // TODO
            'author_email' => '', // TODO
        ];
    }

    /**
     * returns the directory tree of the specified language, as an array of File
     * @return File[]
     * @throws \Exception if unknown bug or file I/O error occurs
     */
    public static function buildTree(): array {
        $result = [];
        $table = TableRegistry::getTableLocator()->get('files');
        if(!($table instanceof FilesTable)) {
            throw new \Exception('failed to get a reference to Table object');
        }
        foreach(LANGUAGES as $lang) {
            $files = self::_buildTreeOfLang($lang, $table);
            $result = array_merge($result, $files);
        }
        return $result;
    }

    /**
     * returns the directory tree of the specified language, as an array of File
     * @param string $lang en, ja, etc...
     * @return File[]
     * @throws \Exception if the $lang is not supported, or file I/O error occurs
     */
    private static function _buildTreeOfLang(string $lang, FilesTable $table): array {
        if(!in_array($lang, LANGUAGES)) {
            throw new \Exception("unsupported language : {$lang}");
        }
        $result = [];
        $filePaths = self::_listFilePaths(self::REPO_DIR . DS . $lang);
        foreach($filePaths as $filePath) {
            $gitLog = self::_gitLog($filePath);
            $entity = $table->newEntity([
                'lang' => $lang,
                'filepath' => $filePath,
                'commit_hash' => $gitLog['commit_hash'],
                'committed_epoch_time' => $gitLog['committed_epoch_time'],
                'author_name' => $gitLog['author_name'],
                'author_email' => $gitLog['author_email'],
                'created_epoch_time' => time(),
            ]);
            $result[] = $entity;
        }
        return $result;
    }

    /**
     * list files recursively
     * @param string $dir SHOULD NOT end with DS(directory separator))
     * @return array array of full file-paths
     */
    private static function _listFilePaths(string $dir): array {
        // TODO: implement
        return [];
    }
}
