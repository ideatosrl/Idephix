<?php
namespace Idephix\File;

use Idephix\File\Node\IdxVariableVisitor;
use Idephix\File\Node\IdxTaskVisitor;
use Idephix\IdxSetupCollector;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;

class FunctionBasedIdxFile implements IdxFile
{

    /**
     * @var Parser
     */
    private $parser;

    public function __construct($file)
    {
        $this->setupCollector = new IdxSetupCollector();

        $this->parser = new Parser(new Lexer());
        $this->traverers = new NodeTraverser();
        $this->traverers->addVisitor(new NameResolver());
        $this->traverers->addVisitor(new IdxVariableVisitor($this->setupCollector));
		$this->traverers->addVisitor(new IdxTaskVisitor($this->setupCollector));

        $stmts = $this->parser->parse(file_get_contents($file));
        $this->traverers->traverse($stmts);
    }

    public function targets()
    {
        return $this->setupCollector->getTargets();
    }

    public function sshClient()
    {
        return $this->setupCollector->getSshClient();
    }

    public function output()
    {
        $this->setupCollector->output();
    }

    public function input()
    {
        $this->setupCollector->input();
    }

    public function tasks()
    {
        return $this->setupCollector->getTasks();
    }

    public function libraries()
    {
        // TODO: Implement libraries() method.
    }

}