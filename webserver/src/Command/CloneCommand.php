<?php
namespace App\Command;

use App\Model\GitRepo;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class CloneCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $isCloned = GitRepo::isCloned();
        if($isCloned) {
            $io->err('Already cloned');
            return static::CODE_ERROR;
        }
        GitRepo::clone();
        $io->out('cloned successfully');
        return static::CODE_SUCCESS;
    }
}
