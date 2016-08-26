<?php

namespace Idephix\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\HelpCommand;
use Idephix\Console\Command\ListCommand;

class Application extends BaseApplicatio implements TaskExecutor
{
    private $logo = <<<'EOD'

  ___    _            _     _
 |_ _|__| | ___ _ __ | |__ (_)_  __
  | |/ _` |/ _ \  _ \|  _  | \ \/ /
  | | (_| |  __/ |_) | | | | |>  <
 |___\__,_|\___| .__/|_| |_|_/_/\_\
               |_|


EOD;

    private $releaseDate;

    private $output;

    private $input;

    private $tasks;

    public function __construct(
      $name = 'UNKNOWN',
      $version = 'UNKNOWN',
      $releaseDate = 'UNKNOWN',
      $input,
      $output)
    {
        parent::__construct($name, $version);

        $this->input = $input;
        $this->output = $output;
        $this->releaseDate = $releaseDate;

        $this->setAutoExit(false);

        $this->getDefinition()->addOption(new InputOption('--config', 'c', InputOption::VALUE_OPTIONAL, 'idxrc file path', getcwd() . '/' .'idxrc.php'));
        $this->getDefinition()->addOption(new InputOption('--file', 'f', InputOption::VALUE_OPTIONAL, 'idxrc file path', getcwd() . '/' . 'idxfile.php'));
        $this->getDefinition()->addOption(new InputOption('--env', null, InputOption::VALUE_REQUIRED, 'Set remote environment.'));
    }

    public function getHelp()
    {
        return $this->logo . parent::getHelp();
    }

    public function getLongVersion()
    {
        if ('UNKNOWN' === $this->getName() ||
            'UNKNOWN' === $this->getVersion()) {
            return '<info>Console Tool</info>';
        }

        $version = sprintf(
            '<info>%s</info> version <comment>%s</comment> released %s',
            $this->getName(),
            $this->getVersion(),
            $this->releaseDate
        );

        return $version;
    }

    protected function getDefaultCommands()
    {
        return array(new HelpCommand(), new ListCommand());
    }

    public function addTask(Task $task)
    {
        $this->tasks[] = $task;
        $this->add(Command::fromTask($task, $this));

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->tasks->has($name) && $this->has($name);
    }

    public function run($context)
    {
        $this->selectEnvironment($context->getConfig());

        $hasErrors = false;
        foreach ($context->getHosts() as $host) {
            $context->openRemoteConnection($host);
            $returnValue = $this->run($this->input, $this->output);
            $hasErrors = $hasErrors || !(is_null($returnValue) || ($returnValue == 0));
            $context->closeRemoteConnection();
        }

        if ($hasErrors) {
            throw new FailedCommandException();
        }
    }

    public function runTask($name, $arguments = array())
    {
        $inputFactory = new InputFactory();

        $input = $inputFactory->buildFromUserArgsForTask(
            func_get_args(),
            $this->tasks->get($name)
        )

        return $this->get($name)->run($input, $this->output);
    }

    protected function selectEnvironment($context)
    {
        $environments = $context->getConfig()->environments();

        if (!$this->input->hasParameterOption('--env')) {
            return;
        }

        $userDefinedEnv = $this->input->getParameterOption(array('--env'));

        if (!isset($environments[$userDefinedEnv])) {
            $msg = sprintf(
                'Wrong environment "%s". Available [%s]',
                $userDefinedEnv,
                implode(', ', array_keys($environments))
            );

            $this->output
                 ->writeln('<error>'.$msg.'</error>');

            exit(1);
        }

        $context->setEnv($userDefinedEnv);
    }
}
