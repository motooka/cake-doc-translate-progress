<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\Entity\File;
use App\Model\Table\FilesTable;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

class GitRepo
{
    const REPO_DIR_PARENT = TMP;
    const REPO_DIR = self::REPO_DIR_PARENT . 'repo';
    const REPO_GIT_URL = 'https://github.com/cakephp/docs.git';

    public static function clone(): void {
        if(file_exists(self::REPO_DIR)) {
            throw new \Exception('already cloned');
        }
        $cwd = getcwd();
        self::_chdirOrFail(self::REPO_DIR_PARENT);

        $repoURL = self::REPO_GIT_URL;
        $repoDIR = self::REPO_DIR;
        $command = "git clone \"{$repoURL}\" \"{$repoDIR}\"";
        $commandOutput = [];
        $commandResultCode = 0;
        self::_exec($command, $commandOutput, $commandResultCode);
        self::_chdirOrFail($cwd);
    }

    public static function pull(): void {
        $cwd = getcwd();
        self::_chdirOrFail(self::REPO_DIR);
        $command = "git pull";
        $commandOutput = [];
        $commandResultCode = 0;
        self::_exec($command, $commandOutput, $commandResultCode);
        self::_chdirOrFail($cwd);
    }

    public static function getCurrentBranch(): string {
        $cwd = getcwd();
        self::_chdirOrFail(self::REPO_DIR);
        $command = "git rev-parse --abbrev-ref HEAD";
        $commandOutput = [];
        $commandResultCode = 0;
        self::_exec($command, $commandOutput, $commandResultCode);
        self::_chdirOrFail($cwd);
        if(count($commandOutput) <= 0) {
            throw new \Exception('failed to get current branch');
        }
        return $commandOutput[0];
    }

    public static function checkoutBranch(string $branch): void {
        $cwd = getcwd();
        self::_chdirOrFail(self::REPO_DIR);
        $command = "git checkout \"$branch\"";
        $commandOutput = [];
        $commandResultCode = 0;
        self::_exec($command, $commandOutput, $commandResultCode);
        self::_chdirOrFail($cwd);
    }

    /**
     * executes "git log -1" command. The caller MUST change the current directory to the repo before call this function.
     * @param string $filePath
     * @return string[]
     * @throws \Exception
     */
    private static function _gitLog(string $filePath): array {
        // format : https://git-scm.com/docs/pretty-formats
        // git log command reference : https://git-scm.com/docs/git-log
        $gitLogFormat = implode('%n', [
            '%H', // full commit hash
            '%ct', // commit time (epoch)
            '%an', // author name
            '%ae', // author email
        ]);
        $command = "git log -1 --format=format:{$gitLogFormat} \"{$filePath}\"";
        $commandOutput = [];
        $commandResultCode = 0;
        self::_exec($command, $commandOutput, $commandResultCode);
        return [
            'commit_hash' => $commandOutput[0] ?? '',
            'committed_epoch_time' => $commandOutput[1] ?? '',
            'author_name' => $commandOutput[2] ?? '',
            'author_email' => $commandOutput[3] ?? '',
        ];
    }

    private static function _exec(string $command, array &$output, int &$result_code): void {
        $cwd = getcwd();
        $execResult = exec($command, $output, $result_code);
        Log::info("OS Command executed. currentDir={$cwd}, command='".$command."', resultCode={$result_code}, output=".print_r($output, true));
        if($execResult === false) {
            throw new \Exception('command execution failed');
        }
        if($result_code != 0) {
            throw new \Exception("non-zero status code : {$result_code}");
        }
    }

    private static function _chdirOrFail(string $dir) {
        $result = chdir($dir);
        if($result === false) {
            throw new \Exception("failed to chdir to {$dir}");
        }
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
        if(!is_dir($dir)) {
            throw new \Exception("{$dir} is not a directory");
        }
        if(!is_executable($dir) || !is_readable($dir)) {
            throw new \Exception("{$dir} is not readable");
        }
        $files = scandir($dir);
        if($files === false) {
            throw new \Exception("failed to list file of directory {$dir}");
        }
        $result = [];
        foreach($files as $file) {
            if(in_array($file, ['.', '..'], true)) {
                continue;
            }
            $fullPath = $dir . DS . $file;
            if(is_dir($fullPath)) {
                $subFiles = self::_listFilePaths($fullPath);
                $result = array_merge($result, $subFiles);
            }
            else {
                $result[] = $fullPath;
            }
        }
        return $result;
    }
}
