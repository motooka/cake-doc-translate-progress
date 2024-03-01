<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Scan | null $latestScan
 */
?>
<fieldset>
    <legend>scan</legend>
    <p>scan the branch to get status of translation</p>
    <?php
    $branchesForSelect = [];
    foreach(BRANCHES as $branchName) {
        $branchesForSelect[$branchName] = $branchName;
    }
    echo $this->Form->create(null, ['url'=>['action'=>'scan'], 'type'=>'POST']);
    echo $this->Form->select('branch', ['options'=>$branchesForSelect]);
    echo $this->Form->submit('scan');
    echo $this->Form->end();

    ?>
</fieldset>

<fieldset>
    <legend>clone</legend>
    <p>Clone the repository if this web application installation doesn&apos;t have a working copy.</p>
    <?php
    echo $this->Form->create(null, ['url'=>['action'=>'clone'], 'type'=>'POST']);
    echo $this->Form->submit('clone');
    echo $this->Form->end();
    ?>
</fieldset>
