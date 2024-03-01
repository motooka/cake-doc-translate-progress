<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\GitRepo;
use App\Model\Table\FilesTable;
use App\Model\Table\ScansTable;
use Cake\Event\EventInterface;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * @property FilesTable $Files
 * @property ScansTable $Scans
 */
class MainController extends AppController
{
    private FilesTable $Files;
    private ScansTable $Scans;
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // TODO: do type-safe programming
        $this->Files = TableRegistry::getTableLocator()->get('files');
        $this->Scans = TableRegistry::getTableLocator()->get('scans');
    }

    public function index(): Response | null
    {
        $isCloned = GitRepo::isCloned();
        $latestScan = null;
        if($isCloned) {
            $latestScan = $this->Scans->getLatestScan();
            if($latestScan && $latestScan->scan_finished_epoch_time !== null) {
                $filesForView = [];
                // TODO
                // $files = $this->Files->
            }
        }
        $this->set('latestScan', $latestScan);
        return null;
    }

    public function clone(): Response
    {
        if(!$this->request->is('POST')) {
            throw new MethodNotAllowedException();
        }
        $isCloned = GitRepo::isCloned();
        if($isCloned) {
            $this->Flash->error('Already cloned');
            return $this->redirect(['action'=>'index']);
        }
        GitRepo::clone();
        $this->Flash->success('Cloned Successfully');
        return $this->redirect(['action'=>'index']);
    }

    public function scan(): Response
    {
        if(!$this->request->is('POST')) {
            throw new MethodNotAllowedException();
        }
        $branch = $this->request->getData('branch');
        if(!in_array($branch, BRANCHES, true)) {
            throw new NotFoundException();
        }
        $this->log("going to scan the branch : {$branch}", LOG_INFO);
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
        $commitDateStr = date('Y-m-d H:i:s', (int)($lastCommit['committed_epoch_time'] ?? 0));
        $this->log("finished scanning the branch : {$branch}, commit time : {$commitDateStr}", LOG_INFO);
        $this->Flash->success('Scanned Successfully');
        return $this->redirect(['action'=>'index']);
    }

}
