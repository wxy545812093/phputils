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
use \Vipkwd\Utils\{Str,Dev};
// use \Exception;

class Console extends Command {
	
	private static $nameSpace = '\\Vipkwd\\Utils\\';
	private static $methodsOrderBy = true;
	private static $showList = true;
	private static $showMethod = false;
	private static $testMethod = false;
	private static $writelnLines = [];
	private static $writelnWidths = [];
	private static $shieldMethods = [
		"__construct",
		"__destruct",
		"__set",
		"__get",
		"__call",
		"__callStatic",
		"__getSuggestion"
	];

	protected function configure(){
		// return $this->__default_configure();
		$this->setName("dump")
			->setDescription('Show the class list of <info>Vipkwd/utils</info> package')
			->setHelp('This command allow you to View/Show the doc of class methods list')
			->addArgument('className', InputArgument::OPTIONAL, 'Show the method list of "className".',"")
			->addOption("method", "m", InputOption::VALUE_OPTIONAL,'Show the "method" method in "className" class.',"")
			->addOption("eg", "", InputOption::VALUE_OPTIONAL,'Test the method in "className" class.',"-")
		;
		// $this->setName("dump:task")
		// 	->setDescription('导入/更新扩展资源')
		// 	->setHelp('导入/更新扩展资源')
		// ;

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
			$className = ucfirst($className);
			if(!file_exists( static::getSrcPath() .'/'.$className.".php")){
				$output->writeln('');
				$output->writeln('[Notice] Class "<info>'.self::$nameSpace.$className.'</info>" not found in Package.');
				$output->writeln('');
				return 1;
			}
			self::$showList = false;

			if($method != ""){
				self::$showMethod = $method;
				self::$testMethod = $eg != "-";
				self::parseClass($className, 0, $classDescript=null);
				self::output($input, $output);
				return 1;
			}
		}
		self::buildMethodListDoc($className);
		self::output($input, $output);
		return 1;

		// return $this->__default_execute($input, $output);
	}

	private function output(&$input, &$output){
		$widths = [
			"Idx" 		=> 0,
			"Namespace" => 0,
			"Class" 	=> 0,
			"Method" 	=> 0,
			"Type" 		=> 0,
			"Arguments" => 0,
			"Eg"		=> 0,
			"Comment" 	=> 40,
		];
		$checker = function($line)use(&$widths){
			foreach($widths as $field => $width){
				$txt = str_replace(['<info>','</info>'],"", strval($line[$field]));
				$len = Str::strLenPlus($txt);
				if($len > $width){
					$widths[$field] = $len;
				}
				unset($len);
			}
		};
		foreach(self::$writelnLines as $idx => $line){
			if(isset($line[1]) && is_array($line[1])){
				$checker($line[1]);
			}else if($line[1] === true){
				$checker(array_combine(array_keys($widths), array_keys($widths)));
			}
		}
		// Dev::dump($widths);
		// Dev::dumper(self::$writelnLines,1);
		self::$writelnWidths = $widths;
		foreach(self::$writelnLines as $line){
			if(is_array($line)){
				$output->writeln(self::createTRLine($line[0], $line[1], $line[2] ?? false));
			}else{
				$output->writeln($line);
			}
		}
	}

	private static function buildMethodListDoc($cmd){
		$path = static::getSrcPath();
		self::$writelnLines[] = ["+", "-"];
		self::$writelnLines[] = ["|",true, true];
		self::$writelnLines[] = ["+", "-"];

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
			self::parseClass( str_replace(".php","", $filename), $index, $classDescript);
		};
		self::$writelnLines[] = ["+", "-"];
	}

	private static function parseClass($class, $index, $classDescript=null){
		$className = self::$nameSpace.$class;
		$class = new \ReflectionClass($className);
		$methods = $class->getMethods(\ReflectionMethod::IS_STATIC + \ReflectionMethod::IS_PUBLIC);
		//剔除未公开的方法
		foreach($methods as $k => $method){
			if($method->isProtected() || $method->isPrivate()){
				unset($methods[$k]);
			}
			unset($k,$method);
		}
		if( self::$showList === true){
			self::$writelnLines[] = ["|", [
				"Idx" => str_pad(strval($index+1), 2, " ", STR_PAD_LEFT),
				"Namespace" => $class->getNamespaceName(),
				"Class" => $class->getShortName(),
				"Method" => "Et: ".count($methods),
				"Type" => "-",
				"Arguments" => "-",
				"Eg" => "-",
				"Comment" => trim($classDescript),
			]];
			// if($class->getShortName() == "Qrcodes"){
			// 	Dev::dumper(self::$writelnLines);
			// 	Dev::dumper($class,1);
			// }
			return null;
		}

		//统计被忽略的方法有多少个
		$methodContinues = 0;
		//已开启 按自然升序 打印类方法
		if(static::$methodsOrderBy){
			$methodsSort = [];
			foreach($methods as $method){
				$methodsSort[ $method->getName() ] = $method;
			}
			ksort($methodsSort);
			if(array_key_exists("instance", $methodsSort)){
				$instance = $methodsSort['instance'];
				unset($methodsSort['instance']);
			}
			$methods = array_values($methodsSort);
			if(isset($instance)){
				array_unshift($methods, $instance);
			}
			unset($methodsSort);
		}
		$__index = 1;
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
			$eg = "[x]";
			foreach($doc as $_eg){
				$_eg = trim($_eg);
				if( ($pos = stripos($_eg, "-e.g:")) > 0 ){
					$eg = "<info>[√]</info>";
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
				$defaults[$position] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : "...NuLl";//TODO 标识必传参数
				// Dev::dump($param->getClass());
				$position++;
			}
			// ------args-------
			$args = "";
			if(!empty($arguments)){
				$args_seper = ', ';
				foreach($arguments as $idx => $field){
					$args .= $args_seper.'$'.$field;
					switch(strtolower(gettype($defaults[$idx]))){
						case "boolean": $args .= ('='.($defaults[$idx] === true ? "true" : "false")); break;
						case "string":
							//TODO 标识必传参数
							if($defaults[$idx] != "...NuLl"){
								$args .= ('="'.$defaults[$idx].'"'); 
							}
							break;
						case "array": 	$args .= ('=[]'); break;
						case "object": 	$args .= ('={}'); break;
						case "null": 	$args .= ('=null'); break;
						default:
							// if ($defaults[$idx] instanceof \Closure){
							// 	$args .= ('=\Closure');
							// }elseif(is_callable($defaults[$idx])){
							// 	$args .= ('=fn()');
							// }else{
								$args .= ('='.$defaults[$idx]);
							// }
							break;
					}
				}
				$args = ltrim($args, $args_seper);
			}

			if(self::$showMethod != false){
				self::$writelnLines[] = ["+", "-"];
				self::$writelnLines[] = ["|",true, true];
				self::$writelnLines[] = ["+", "-"];
			}
			self::$writelnLines[] = ["|", [
				"Idx" => str_pad(strval($__index++), 2, " ", STR_PAD_LEFT),
				"Namespace" => $class->getNamespaceName(),
				"Class" => $class->getShortName(),
				"Method" => $method->getName(),
				"Type" => $method->isStatic() ? "static" : "public",
				"Arguments" => $args,
				"Eg" => $eg,
				"Comment" => $doc,
			]];
			
			if(self::$showMethod !== false){
				self::$writelnLines[] = ["+", "-"];
				self::$writelnLines[] = "";
				if($comment != ""){
					$comment = preg_replace("|(\ +)\/\*\*|","/**", $comment);
					$comment = preg_replace("|(\ +)\*|"," *", $comment);
					self::$writelnLines[] = $comment;
				}

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
				self::$writelnLines[] = $text;
				foreach($args as $var){
					self::$writelnLines[] = "    {$var}";
				}
				!empty($args) && self::$writelnLines[] = ")";

				self::$writelnLines[] = "";
				self::$writelnLines[] = self::createTDText(100);
				self::$writelnLines[] = "";
				break;
			}
		}
		//类中没有枚举到指定方法(或级别不是 public|static)；
		if(count($methods) == $methodContinues && self::$showMethod !== false){
			self::$writelnLines[] = ( "-- !!! Warning: <info>".$className."::".self::$showMethod ."()</info> method does not exist or does not expose access rights.");
		}
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
					$_eg = preg_replace("/(\ +)=(\ +)/","=", $_eg);
					$_eg = rtrim($_eg,";").";";
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
			"Comment" 	=> 50,
		];
		$list = [];
		$list[] ="";
		foreach(self::$writelnWidths as $title => $with){
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
			// $len -= 2;
		}
		$txt = Str::strPadPlus($txt, $len, $septer);
		if($setColor === true){
			//$txt = str_pad($txt, $len, $septer, STR_PAD_BOTH);
			$txt = "<info>" .$txt. "</info>";
		}
		if($septer != "-") $txt = " {$txt} ";
		else $txt= "-{$txt}-";
		return $txt;
	}

	private static function shieldMethod(string $method, string $comment):bool{
		$has = preg_match("/@type\ +public/i", $comment);
		return in_array($method, self::$shieldMethods) && !$has;
	}

	private static function getSrcPath(){
		return VIPKWD_UTILS_LIB_ROOT ."/src/";
	}
}