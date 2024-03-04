<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\GitRepo;
use App\Model\Table\FilesTable;
use App\Model\Table\ScansTable;
use Cake\Event\EventInterface;
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
        $this->Files = TableRegistry::getTableLocator()->get('Files');
        $this->Scans = TableRegistry::getTableLocator()->get('Scans');
    }

    public function index(string $lang = null): Response | null
    {
        $isCloned = GitRepo::isCloned();
        if(!$isCloned) {
            $this->Flash->error('The repository is not cloned yet');
            return $this->redirect('/');
        }
        $latestScan = $this->Scans->getLatestScan();
        if(empty($latestScan)) {
            $this->Flash->error('The repository is not scanned yet');
            return $this->redirect('/');
        }
        $status = $this->Files->getTranslationStatus($lang);
        $this->set('lang', $lang);
        $this->set('latestScan', $latestScan);
        $this->set('status', $status);
        return null;
    }

    public function download(string $lang = null): Response
    {
        $isCloned = GitRepo::isCloned();
        if(!$isCloned) {
            $this->Flash->error('The repository is not cloned yet');
            return $this->redirect('/');
        }
        $latestScan = $this->Scans->getLatestScan();
        if(empty($latestScan)) {
            $this->Flash->error('The repository is not scanned yet');
            return $this->redirect('/');
        }
        $status = $this->Files->getTranslationStatus($lang);
        $LineEnd = "\n";
        $csv = $this->_buildCsvRow([
                'FilePath',
                'English Last Commit Hash',
                'English Last Commit YYYY-MM-DD',
                'Translation Last Commit Hash',
                'Translation Last Commit YYYY-MM-DD',
            ]).$LineEnd;
        foreach($status as $row) {
            $fields = [
                $row['filepath'] ?? '',
                $row['en_commit_hash'] ?? '',
                $this->_getYMD($row['en_committed_epoch_time'] ?? null),
                $row['trans_commit_hash'] ?? '',
                $this->_getYMD($row['trans_committed_epoch_time'] ?? null),
            ];
            $csv .= $this->_buildCsvRow($fields).$LineEnd;
        }

        $shortCommitHash = substr($latestScan->commit_hash, 0, 8);
        $filename = "cakephp-translation-diff-{$shortCommitHash}-{$lang}.csv";

        return $this->response->withStatus(200)
            ->withType('text/csv')
            ->withStringBody($csv)
            ->withDownload($filename);
    }

    private function _getYMD(int $epoch = null): string
    {
        if($epoch === null) {
            return '';
        }
        $d = new \DateTime();
        $d->setTimestamp($epoch);
        return $d->format('Y-m-d');
    }

    private function _buildCsvRow(array $fields): string
    {
        $line = implode('","', $fields);
        return '"'.$line.'"';
    }
}
