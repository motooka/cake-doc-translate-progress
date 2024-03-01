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
        if(self::isCloned()) {
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

    public static function isCloned(): bool {
        return file_exists(self::REPO_DIR);
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

    public static function checkoutBranch(string $branch): void {
        $cwd = getcwd();
        self::_chdirOrFail(self::REPO_DIR);
        $command = "git checkout \"$branch\"";
        $commandOutput = [];
        $commandResultCode = 0;
        self::_exec($command, $commandOutput, $commandResultCode);
        self::_chdirOrFail($cwd);
    }

    public static function getLatestCommit()
    {
        return self::_gitLog(".");
    }

    /**
     * executes "git log -1" command. The caller MUST change the current directory to the repo before call this function.
     * @param string $filePath
     * @return string[]
     * @throws \Exception
     */
    private static function _gitLog(string $relativeFilePathFromRepoRoot): array {
        // format : https://git-scm.com/docs/pretty-formats
        // git log command reference : https://git-scm.com/docs/git-log
        $gitLogFormat = implode('%n', [
            '%H', // full commit hash
            '%ct', // commit time (epoch)
            '%an', // author name
            '%ae', // author email
        ]);
        $command = "git log -1 --format=format:{$gitLogFormat} \"{$relativeFilePathFromRepoRoot}\"";
        $commandOutput = [];
        $commandResultCode = 0;
        self::_exec($command, $commandOutput, $commandResultCode);
        return [
            'commit_hash' => $commandOutput[0] ?? 'dummy-commit',
            'committed_epoch_time' => (int)($commandOutput[1] ?? 0),
            'author_name' => $commandOutput[2] ?? 'dummy author',
            'author_email' => $commandOutput[3] ?? 'dummy email',
        ];
    }

    private static function _exec(string $command, array &$output, int &$result_code, bool $withLog = true): void {
        $cwd = getcwd();
        $execResult = exec($command, $output, $result_code);
        if($withLog) {
            Log::info("OS Command executed. currentDir={$cwd}, command='".$command."', resultCode={$result_code}, output=".print_r($output, true));
        }
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
        $cwd = getcwd();
        self::_chdirOrFail(self::REPO_DIR);
        $result = [];
        $table = TableRegistry::getTableLocator()->get('files');
        if(!($table instanceof FilesTable)) {
            throw new \Exception('failed to get a reference to Table object');
        }
        foreach(LANGUAGES as $lang) {
            $files = self::_buildTreeOfLang($lang, $table);
            $result = array_merge($result, $files);
        }
        self::_chdirOrFail($cwd);
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
        $filePathsRelative = self::_listRelativeFilePaths($lang, '');
        foreach($filePathsRelative as $filePathRelative) {
            $gitLog = self::_gitLog($lang . DS . $filePathRelative);
            $entity = $table->newEntity([
                'lang' => $lang,
                'filepath' => $filePathRelative,
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
     * @param string $lang language
     * @param string $dirRelativeFromLangRoot SHOULD NOT contain trailing DS(directory separator)
     * @return array array of file-paths, relative from the language directory
     * @throws \Exception
     */
    private static function _listRelativeFilePaths(string $lang, string $dirRelativeFromLangRoot): array {
        $dirFullPath = self::REPO_DIR.DS.$lang.(empty($dirRelativeFromLangRoot) ? '' : (DS . $dirRelativeFromLangRoot));
        if(!is_dir($dirFullPath)) {
            throw new \Exception("{$dirFullPath} is not a directory");
        }
        if(!is_executable($dirFullPath) || !is_readable($dirFullPath)) {
            throw new \Exception("{$dirFullPath} is not readable");
        }
        $files = scandir($dirFullPath);
        if($files === false) {
            throw new \Exception("failed to list file of directory {$dirFullPath}");
        }
        $result = [];
        foreach($files as $file) {
            if(in_array($file, ['.', '..'], true)) {
                continue;
            }
            $fileFullPath = $dirFullPath . DS . $file;
            $fileRelativeFromLangRoot = $dirRelativeFromLangRoot . DS . $file;
            if(is_dir($fileFullPath)) {
                $subFiles = self::_listRelativeFilePaths($lang, $fileRelativeFromLangRoot);
                $result = array_merge($result, $subFiles);
            }
            else {
                $result[] = $fileRelativeFromLangRoot;
            }
        }
        return $result;
    }
}
