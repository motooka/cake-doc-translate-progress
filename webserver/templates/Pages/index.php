<?php
/**
 * @var \App\View\AppView $this
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Error\Debugger;
use Cake\Http\Exception\NotFoundException;

?>
<h2>About</h2>
<p>
    This site scans <a target="_blank" rel="noopener" href="https://github.com/cakephp/docs/">cakephp/docs repository</a> daily, and builds "diff" between English and other translated languages.
    <br/>
    You can clone or fork <a target="_blank" rel="noopener" href="https://github.com/motooka/cake-doc-translate-progress/">the source code</a> under the license.
</p>

<h2>For the Administrator of this Site</h2>
<p>
    <code>git clone</code> and <code>git pull</code> should be invoked via CakePHP CLI commands.
    You should clone first, and set up a scheduler (like crontab).
</p>
<ul>
    <li>
        <code>bin/cake clone</code> : executes <code>git clone</code> into <code>tmp/repo</code> directory.
    </li>
    <li>
        <code>bin/cake scan</code> : executes <code>git pull</code> and builds diff.
    </li>
</ul>
