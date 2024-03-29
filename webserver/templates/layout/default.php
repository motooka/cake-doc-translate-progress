<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'cake', 'app']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>">CakePHP Docs Translation Progress</a>
        </div>
        <div class="top-nav-links">
            <a target="_blank" rel="noopener" href="https://github.com/motooka/cake-doc-translate-progress/">Source Code on GitHub</a>
        </div>
    </nav>
    <nav class="lang-nav">
        <ul>
            <?php
            foreach(LANGUAGES as $lang) {
                if($lang === 'en') {
                    continue;
                }
                ?>
                <li>
                    <?php
                    if(str_ends_with($this->request->getUri(), '/'.$lang)) {
                        echo '<b>'.LANGUAGE_NAMES[$lang].'</b>';
                    }
                    else {
                        echo $this->Html->link(LANGUAGE_NAMES[$lang], '/'.$lang);
                    }
                    ?>
                </li>
                <?php
            }
            ?>
        </ul>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
        Copyright &copy; T. MOTOOKA
    </footer>
</body>
</html>
