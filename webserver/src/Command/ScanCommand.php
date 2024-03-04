<?php
namespace App\Command;

use App\Model\GitRepo;
use App\Model\Table\FilesTable;
use App\Model\Table\ScansTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;

class ScanCommand extends Command
{
    private FilesTable $Files;
    private ScansTable $Scans;
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $isCloned = GitRepo::isCloned();
        if(!$isCloned) {
            $io->err('Clone the repository first');
            return static::CODE_ERROR;
        }

        $branch = '5.x';

        // TODO: do type-safe programming
        $this->Files = TableRegistry::getTableLocator()->get('Files');
        $this->Scans = TableRegistry::getTableLocator()->get('Scans');

        $this->log("Going to scan the branch {$branch}", LOG_INFO);

        $conn = $this->Scans->getConnection();

        // transaction 1 : start new scan
        $conn->begin();
        try {
            // insert a new record into "scans" with scanning status
            $scan = $this->Scans->newEntity([
                'branch' => $branch,
                'commit_hash' => 'scanning...',
                'committed_epoch_time' => 0,
                'created_epoch_time' => time(),
            ]);
            $this->Scans->saveOrFail($scan);

            $runningScans = $this->Scans->query()->where([
                'scan_finished_epoch_time IS NULL',
            ])->toArray();
            if(count($runningScans) < 1) {
                throw new \Exception('failed to write records on the table "scans".');
            }
            if(count($runningScans) > 1) {
                throw new \Exception('multiple processes are scanning simultaneously');
            }
            if($scan->id != $runningScans[0]->id) {
                throw new \Exception('database corruption detected');
            }
            $conn->commit();
        }
        catch(\Exception $e) {
            $conn->rollback();
            throw $e;
        }

        // git operations
        GitRepo::checkoutBranch($branch);
        GitRepo::pull();
        $lastCommit = GitRepo::getLatestCommit();
        $files = GitRepo::buildTree();

        // transaction 2 : save files and finish scanning
        $conn->begin();
        try {
            // operation 1 : lock the record of scans
            $scan2 = $this->Scans->get($scan->id);
            if($scan2->scan_finished_epoch_time !== null) {
                throw new \Exception('Another process already scanned');
            }
            $this->Scans->patchEntity($scan2, [
                'commit_hash' => 'still scanning...',
            ]);
            $this->Scans->saveOrFail($scan2);

            // operation 2 : delete all record of "files" table : "truncate table" is not supported on SQLite
            $this->Files->deleteAll([]);

            // operation 3 : insert into files
            foreach($files as $file) {
                $this->Files->saveOrFail($file);
            }

            // operation 4 : write scan information
            $this->Scans->patchEntity($scan2, [
                'branch' => $branch,
                'commit_hash' => $lastCommit['commit_hash'],
                'author_name' => $lastCommit['author_name'],
                'author_email' => $lastCommit['author_email'],
                'committed_epoch_time' => $lastCommit['committed_epoch_time'],
                'scan_finished_epoch_time' => time(),
            ]);
            $this->Scans->saveOrFail($scan2);

            $conn->commit();
        }
        catch(\Exception $e) {
            $conn->rollback();
            throw $e;
        }

        $io->out('scanned successfully');
        $this->log("Scanned {$branch} successfully", LOG_INFO);
        return static::CODE_SUCCESS;
    }
}
