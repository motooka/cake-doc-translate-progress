<?php
namespace App\View\Helper;

use Cake\View\Helper;

class CommitHelper extends Helper
{
    const COMMIT_URI_BASE = 'https://github.com/cakephp/docs/commit/';

    public function commit(string $commitHash = null, int $epochTime = null): string
    {
        if(empty($commitHash)) {
            return 'Not Available';
        }
        $shortCommitHash = substr($commitHash, 0, 8);
        $commitURL = self::COMMIT_URI_BASE . $commitHash;
        $commitDateStr = '';
        if($epochTime !== null) {
            $commitDate = new \DateTime();
            $commitDate->setTimestamp($epochTime);
            $commitDateStr = $commitDate->format('Y-m-d');
        }
        return <<<EOHTML
<a target="_blank" rel="noopener" href="$commitURL">
    $shortCommitHash
    |
    $commitDateStr
</a>
EOHTML;
    }
}
