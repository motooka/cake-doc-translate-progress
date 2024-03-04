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
        $statuses = [];
        if($isCloned) {
            $latestScan = $this->Scans->getLatestScan();
            if($latestScan && $latestScan->scan_finished_epoch_time !== null) {
                foreach(LANGUAGES as $lang) {
                    if ($lang === 'en') {
                        continue;
                    }
                    $statuses[$lang] = $this->Files->getTranslationStatus($lang);
                }
            }
        }
        $this->set('latestScan', $latestScan);
        $this->set('statuses', $statuses);
        return null;
    }
}
