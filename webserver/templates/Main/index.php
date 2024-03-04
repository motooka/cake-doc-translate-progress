<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Scan | null $latestScan
 * @var array | null $statuses
 */
?>
<fieldset>
    <legend>translation status</legend>
    <ul>
        <li>branch : <?= $latestScan->branch ?? '' ?></li>
        <li>commit hash : <?= $latestScan->commit_hash ?? '' ?></li>
        <li>committed : <?= $latestScan->committed_epoch_time ?? '' ?></li>
    </ul>
    <?php
    if(empty($statuses)) {
        ?>
        <?php
    }
    else {
        ?>
        <?php
    }
    ?>
</fieldset>
