<?php
/**
 * @var \App\View\AppView $this
 * @var string $lang
 * @var \App\Model\Entity\Scan | null $latestScan
 * @var array | null $status
 */
?>
<fieldset>
    <legend>Scan Summary</legend>
    <ul>
        <li>Branch : <?= $latestScan->branch ?? '' ?></li>
        <li>
            Last Commit :
            <div style="display: inline-block; vertical-align: top;">
                <?= $this->Commit->commit($latestScan->commit_hash, $latestScan->committed_epoch_time) ?>
            </div>
        </li>
    </ul>
</fieldset>
<fieldset>
    <legend>diffs between en and <?= $lang ?></legend>
    <table>
        <thead>
        <tr>
            <th>
                Filepath
                <br/>
                relative from language directory
            </th>
            <th>English<br/>Last Commit</th>
            <th>Translation<br/>Last Commit</th>
            <th>Date Diff</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($status as $entry) {
            ?>
            <tr>
                <td>
                    <?= $entry['filepath'] ?>
                </td>
                <td>
                    <?=
                    $this->Commit->commit($entry['en_commit_hash'], $entry['en_committed_epoch_time']);
                    ?>
                </td>
                <td>
                    <?=
                    $this->Commit->commit($entry['trans_commit_hash'], $entry['trans_committed_epoch_time']);
                    ?>
                </td>
                <td>
                    <?php
                    $epochEn = $entry['en_committed_epoch_time'];
                    $epochTr = $entry['trans_committed_epoch_time'];
                    if(empty($epochEn) || empty($epochTr)) {
                        echo '-';
                    }
                    else if($epochEn <= $epochTr) {
                        echo 'Translation looks Newer';
                    }
                    else {
                        $diffInSeconds = $epochEn - $epochTr;
                        $diffInDays = floor($diffInSeconds / 86400.0);
                        $diffInDaysStr = number_format($diffInDays);
                        echo "{$diffInDaysStr} days old";
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</fieldset>
