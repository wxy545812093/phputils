<?php

/**
 * @author vipkwd <service@vipkwd.com>
 * @link https://github.com/wxy545812093/phputils
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @copyright The PHP-Tools
 */
declare(strict_types = 1);

namespace Vipkwd\Utils\Libs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vipkwd\Utils\Str;
use Vipkwd\Utils\Dev;
// use \Exception;

class Console extends Command {
	
	private static $showList = true;
	private static $showMethod = false;
	private static $testMethod = false;
	private static $shieldMethods = [
		"__construct",
		"__destruct",
		"__set",
		"__get",
		"__call"
	];

	private static function getConsoleName(){
		return "dump";
	}

	protected function configure(){
		// return $this->__default_configure();
		$this->setName( self::getConsoleName() )
			->setDescription('Show the class list of <info>Vipkwd-Utils</info> package')
			->setHelp('This command allow you to View/Show the doc of class methods list')
			->addArgument('className', InputArgument::OPTIONAL, 'Show the method list of "className".',"")
			->addOption("method", "m", InputOption::VALUE_OPTIONAL,'Show the "method" method in "className" class.',"")
			->addOption("eg", "", InputOption::VALUE_OPTIONAL,'Test the method in "className" class.',"-")
		;

	}
	protected function execute(InputInterface $input, OutputInterface $output){

		// 你想要做的任何操作
		$className = trim($input->getArgument('className'));
		$method = trim($input->getOption('method') ?? "");
		$eg = trim($input->getOption('eg') ?? "");

		//纠正短选项使用了 长选项的等于号("=") 问题;
		$method = str_replace('=',"", $method);
		$eg = str_replace('=',"", $eg);
		self::$showList = true;
		self::$showMethod = false;	
		if($className == ""){
			$className = "list";
		}else{
			$path = realpath(__DIR__."/../");
			$className = ucfirst($className);
			if(!file_exists($path.'/'.$className.".php")){
				$output->writeln('');
				$output->writeln('[Notice] Class "<info>'.$className.'</info>" not found in Package.');
				$output->writeln('');
				return 1;
			}
			self::$showList = false;

			if($method != ""){
				self::$showMethod = $method;
				self::$testMethod = $eg != "-";
				self::parseClass($className, $input, $output, 0, $classDescript=null);
				return 1;
			}
		}
		self::buildMethodListDoc($input, $output, $className);
		return 1;

		// return $this->__default_execute($input, $output);
	}

	private static function buildMethodListDoc(&$input, &$output, $cmd){
		$path = realpath(__DIR__."/../");
		$output->writeln(self::createTRLine("+", "-"));
		$output->writeln(self::createTRLine("|",true, true));
		$output->writeln(self::createTRLine("+", "-"));

		foreach(glob($path ."/*.php") as $index => $classFile){
			$_classFile = preg_replace("|[A-Za-z0-9\._\-]+|",'',str_replace($path, '', $classFile));
			if($_classFile != "/"){
				continue;
			}
			unset($_classFile);
			$classDescript= "#";
			$classFile = str_replace('\\','/', $classFile);
			if(self::$showList === false){
				if( substr($classFile, 0 - strlen("{$cmd}.php") ) != "{$cmd}.php" ){
					continue;
				}
			}else{
				preg_match("/@name\ ?(.*)".PHP_EOL."/", file_get_contents($classFile), $match);
				if(isset($match[1])){
					$classDescript = $match[1];//preg_replace("/@name\ ?/", "", $match[0]);
				}
				// Dev::console($match);
			}
			$classFile = explode("/", $classFile);
			$filename=array_pop($classFile);
			unset($classFile);
			self::parseClass( str_replace(".php","", $filename), $input, $output, $index, $classDescript);
		};
		$output->writeln(self::createTRLine("+", "-"));
	}

	private static function parseClass($class, &$input, &$output, $index, $classDescript=null){
		$className = str_replace('Libs', $class, __NAMESPACE__);
		$class = new \ReflectionClass($className);
		$methods = $class->getMethods(\ReflectionMethod::IS_STATIC + \ReflectionMethod::IS_PUBLIC);
		//剔除未公开的方法
		foreach($methods as $k => $method){;
			if($method->isProtected() || $method->isPrivate()){
				unset($methods[$k]);
			}
			unset($k,$method);
		}
		if( self::$showList === true){
			$output->writeln(self::createTRLine("|", [
				"Idx" => ($index+1)."",
				"Namespace" => $class->getNamespaceName(),
				"Class" => $class->getShortName(),
				"Method" => "Total: ".count($methods),
				"Type" => "#",
				"Arguments" => "#",
				"Eg" => "#",
				"Comment" => $classDescript,
			]));
			return;
		}

		//统计被忽略的方法有多少个
		$methodContinues = 0;
		//遍历所有的方法
		foreach ($methods as $index => $method) {
			$comment = $method->getDocComment();

			if( self::shieldMethod($method->getName(), "$comment") || (self::$showMethod !== false && $method->getName() != self::$showMethod )){
				$methodContinues ++;
				continue;
			}
			//获取并解析方法注释
			$doc = explode("\r\n", is_string($comment)? $comment : "-");
			if(count($doc) < 2){
				$doc = explode("\n", is_string($comment)? $comment : "\n--");
			}
			if( self::phpunit($doc) ){
				break;
			}
			//检测 测试用例支持情况
			$eg = "<info>[x]</info>";
			foreach($doc as $_eg){
				$_eg = trim($_eg);
				if( ($pos = stripos($_eg, "-e.g:")) > 0 ){
					$eg = "[√]";
					break;
				}
			}
			$doc = str_replace(["/**","*"," "],"", trim( $doc[1] ?? "" ));

			//获取方法的类型
			//$method_flag = $method->isProtected();//还可能是public,protected类型的
			//获取方法的参数
			$params = $method->getParameters();
			//print_r($params);
			$position=0;    //记录参数的次序
			$arguments=[];
			$defaults=[];
			foreach ($params as $param){
				$arguments[$position] = $param->getName();
				//参数是否设置了默认参数，如果设置了，则获取其默认值
				$defaults[$position] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL;
				$position++;
			}

			// ------args-------
			$args = "";
			if(!empty($arguments)){
				foreach($arguments as $idx => $field){
					$args .=',$'.$field;
					switch(strtolower(gettype($defaults[$idx]))){
						case "boolean": $args .= ('='.($defaults[$idx] === true ? "true" : "false")); break;
						case "string": 	$args .= ('="'.$defaults[$idx].'"'); break;
						case "array": 	$args .= ('=[]'); break;
						case "object": 	$args .= ('={}'); break;
						
						case "null": 	break;
						// case "null": 	$args .= ('=null'); break;

						default: 		$args .= ('='.$defaults[$idx]); break;
					}
				}
				$args = ltrim($args, ', ');
			}

			if(self::$showMethod != false){
				$output->writeln(self::createTRLine("+", "-"));
				$output->writeln(self::createTRLine("|",true, true));
				$output->writeln(self::createTRLine("+", "-"));
			}

			$output->writeln(self::createTRLine("|", [
				"Idx" => ($index+1)."",
				"Namespace" => $class->getNamespaceName(),
				"Class" => $class->getShortName(),
				"Method" => $method->getName(),
				"Type" => $method->isStatic() ? "static" : "public",
				"Arguments" => $args,
				"Eg" => $eg,
				"Comment" => $doc,
			]));
			
			if(self::$showMethod !== false){
				$output->writeln(self::createTRLine("+", "-"));
				$output->writeln("");
				if($comment != ""){
					$comment = preg_replace("|(\ +)\/\*\*|","/**", $comment);
					$comment = preg_replace("|(\ +)\*|"," *", $comment);
					$output->writeln($comment);
				}
				// $output->writeln(self::createTDText(100));
				// $output->writeln("");
				if($method->isStatic()){
					$text = "<info>{$className}</info>::<info>".$method->getName()."</info>";
				}else{
					if($method->getName() == "__construct"){
						$text = "<info>new {$className}</info></info>";
					}else{
						$text = "(<info>{$className}</info> Object)-><info>".$method->getName()."</info>";
					}
				}
				if($args == ""){
					$text .= "()";
					$args = [];
				}else{
					$text.="(";
					$args = explode(',', $args);
				}
				$output->writeln($text);
				foreach($args as $var){
					// $output->writeln("");
					$output->writeln("    {$var}");
				}
				!empty($args) && $output->writeln(")");

				$output->writeln("");
				$output->writeln(self::createTDText(100));
				$output->writeln("");
				break;
			}
		}
		//类中没有枚举到指定方法(或级别不是 public|static)；
		if(count($methods) == $methodContinues && self::$showMethod !== false){
			$output->writeln( "-- !!! Warning: <info>".$className."::".self::$showMethod ."()</info> method does not exist or does not expose access rights.");
		}
		return ;
	}

	/**
	 * 执行测试用例
	 *
	 * @param [type] $doc
	 * @return void
	 */
	private static function phpunit($doc){
		if(self::$testMethod){
			$_console = true;
			$idx = 1;
			foreach($doc as $_eg){
				$_eg = trim($_eg);
				if( ($pos = stripos($_eg, "-e.g:")) > 0 ){
					$_eg = trim( substr($_eg, $pos+5 ));
					if( $_eg[0] == "'" || $_eg[0] == '"'){
						eval("$_eg");
					}else{
						if($_console === true){
							$_console = (str_pad("", 100, "-") )." \r\n";
							echo $_console;
							echo "\r\n";
						}
						if(substr($_eg, 0, 1) == '$'){
							$_eg = "echo '[•] {$_eg}';{$_eg}";
						}

						if( preg_match("/(echo ['\"])/i", $_eg)){
							$_eg = preg_replace("/(echo ['\"])/i","$1 ", $_eg);
						}else if(preg_match("/phpunit\(/", $_eg)){
							$_eg = "\\Vipkwd\\Utils\\Dev::${_eg}";
						}

						echo "[". str_pad("$idx", 2, "0", STR_PAD_LEFT). "]";
						\Vipkwd\Utils\Dev::console(eval("$_eg"), false, false);
						$idx++;
					}
				}
			}
			if($_console !== true){
				echo $_console;
			}else{
				echo " -- [x] 没有提供测试用例或 “".self::$showMethod."” 方法不支持静态调用。\r\n";
			}
			return true;
		}
		return false;
	}

	private static function createTRLine(string $septer, $data=" ", $isTitle=false){
		$conf = [
			"Idx" 		=> 5,
			"Namespace" => 14,
			"Class" 	=> 16,
			"Method" 	=> self::$showList === true ? 20 : 25,
			"Type" 		=> 8,
			"Arguments" => self::$showList === true ? 11 : 76,
			"Eg"		=> 5,
			"Comment" 	=> 40,
		];
		$list = [];
		$list[] ="";
		foreach($conf as $title => $with){
			if($isTitle === true){
				$field = $title;
			}else{
				$field = (is_array($data)) ? @$data[$title] : $data;
			}
			$list[] = self::createTDText( $with, $field, $isTitle === true );
		}
		$list[] = "";
		return implode($septer, $list);
	}

	private static function createTDText(int $len, string $txt ="-", bool $setColor=false){
		$septer = "-";
		if($txt != "-"){
			$septer = " ";
			$len -= 2;
		}
		$txt = Str::strPadPlus($txt, $len, $septer);
		if($setColor === true){
			//$txt = str_pad($txt, $len, $septer, STR_PAD_BOTH);
			$txt = "<info>" .$txt. "</info>";
		}
		if($septer != "-") $txt = " {$txt} ";
		return $txt;
	}

	private static function shieldMethod(string $method, string $comment):bool{
		$has = preg_match("/@type\ +public/i", $comment);
		return in_array($method, self::$shieldMethods) && !$has;
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