<?php

/**
 * @author vipkwd <service@vipkwd.com>
 * @link https://github.com/wxy545812093/vipkwd-phputils
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @copyright The PHP-Tools
 */
declare(strict_types = 1);

namespace Vipkwd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\{
	InputOption,
	InputArgument,
	InputInterface
};
use Vipkwd\Utils\{Str,Dev};
// use \Exception;

class TaskLoad extends Command {
	
	protected function configure(){
		$this->setName("task:load")
			->setDescription('Install/update assetes for utils')
			->setHelp('Install/update assetes for utils')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		include_once(__DIR__.'/../VipkwdTask.php');
		return 1;
	}

	/**
	 * console的标准配置demo
	 *
	 * @return void
	 */
	private function __default_configure(){
		// 命令的名称 ("php artisan" 后面的部分)
		// 运行 "php artisan list" 时的简短描述
		// 运行命令时使用 "--help" 选项时的完整命令描述
		// 配置一个参数
		// 配置一个可选参数
		$this->setName('model:create')
			->setDescription('Create a new model')
			->setHelp('This command allow you to create models...')
			->addArgument('name', InputArgument::REQUIRED, 'what\'s model you want to create?')
			->addArgument('optional', InputArgument::OPTIONAL, 'this is a optional argument', "")
			->addOption("show", null, InputOption::VALUE_OPTIONAL,"Overwrite the argument 'show'")
			;
	}
	/**
	 * console的标准响应demo
	 *
	 * @param object $input
	 * @param object $output
	 * @return int
	 */
	private function __default_execute(&$input, &$output){
		// 你想要做的任何操作
		$optional_argument = $input->getArgument('optional');
		$output->writeln('creating...');
		$output->writeln('created ' . $input->getArgument('name') . ' model success !');
		if ($optional_argument){
			$output->writeln('optional argument is ' . $optional_argument);
		}
		$output->writeln('<info>the end.</info>'.$input->getOption('show'));
		return 1;
	}
}