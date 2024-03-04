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
        $this->Files = TableRegistry::getTableLocator()->get('files');
        $this->Scans = TableRegistry::getTableLocator()->get('scans');
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
}
