<?php

final class Shell
{


    const italic = 'italic';
    const def = 'default';
    const black = 'black';
    const error = 'red';
    const red = 'red';
    const green = 'green';
    const blue = 'blue';
    const magenta = 'magenta';
    const cyan = 'cyan';
    const gray = 'light_gray';
    const light_gray = 'light_gray';
    const light_red = 'light_red';
    const light_green = 'light_green';
    const light_blue = 'light_blue';
    const light_magenta = 'light_magenta';
    const light_yellow = 'light_yellow';
    const dark_gray = 'dark_gray';
    const light_cyan = 'light_cyan';
    const info = 'yellow';
    const yellow = 'yellow';
    const underline = 'underline';
    const bold = 'bold';
    const white = 'white';


    const dark = 'dark';
    const blink = 'blink';
    const reverse = 'reverse';
    const concealed = 'concealed';

    const bg_red = 'bg_red';
    const bg_green = 'bg_green';
    const bg_gray = 'bg_gray';
    const bg_dark_gray = 'bg_dark_gray';
    const bg_light_red = 'bg_light_red';
    const bg_light_green = 'bg_light_green';
    const bg_light_yellow = 'bg_light_yellow';
    const bg_light_blue = 'bg_light_blue';
    const bg_light_magenta = 'bg_light_magenta';
    const bg_light_cyan = 'bg_light_cyan';
    const bg_white = 'bg_white';

    /**
     * Verbosity levels
     */
    const VERBOSITY_QUIET = 0;
    const VERBOSITY_NORMAL = 1;
    const VERBOSITY_INFO = 2;
    const VERBOSITY_DEBUG = 3;

    private static $_verbosity = self::VERBOSITY_NORMAL;

    private static $_init = false;
    private static $_name = false;
    private static $_alertMail = false;
    private static $_logFile = false;
    private static $_logFilePath = false;
    private static $_logFilePrefix = false;
    private static $_pidFilePrefix = false;
    private static $_pidFilePath = false;
    private static $_messages = false;
    private static $_isArgInits = false;
    private static $_pidListCommands = array();
    private static $_arg = array();
    private static $_maxTimeMins = -1;//13*60;
    private static $_pid = null;
    private static $_isMakePid = false;
    private static $_exitCodeOnPidExists = 2;


    public static function setPathPid($path, $prefix = '')
    {
        self::$_pidFilePath = $path;
        self::$_pidFilePrefix = $prefix;
    }

    public static function getLogFile()
    {
        return self::$_logFile;
    }


    public static function getName()
    {
        return self::$_name;
    }
    public static function setPathLog($path, $prefix = '')
    {
        self::$_logFilePath=$path;
        self::$_logFilePrefix=$prefix;
    }

    public static function alertMail($mail)
    {
        self::$_alertMail = $mail;
    }

    public static function dir($dir)
    {
        chdir($dir);
    }
    public static function name($name)
    {
        self::$_name = $name;
    }

    public static function getEnvironment()
    {
        if (isset($_ENV['KUBERNETES_ENVIRONMENT']))
        {
            $e=$_ENV['KUBERNETES_ENVIRONMENT'];
            if (is_string($e)) return $e;
        }
        return self::get('environment'); // or see --environment=[prod/dev]
    }
    public static function isKubernetes()
    {
        if (self::get('kubernetes')) return true;
        if (self::get('k8s')) return true;
        if (isset($_ENV['KUBERNETES_SERVICE_HOST'])) return true;
        return false;
    }

    public static function isInteractive()
    {
        if (self::isKubernetes()) return true;
        if (self::get('cron')) return true;
        return defined("STDOUT") && posix_isatty(STDOUT);
    }

    static public function setPidCommands($commandsArray)
    {
        self::$_pidListCommands = array_merge($commandsArray, self::$_pidListCommands);

    }

    /**
     * In max Execution Time in minutes
     *
     * @param null $seconds
     * @return int
     */
    public static function maxExecutionMinutes($minutes = null)
    {

        if ($minutes) {
            set_time_limit(60 * $minutes);
            self::$_maxTimeMins = $minutes;
        }
        return self::$_maxTimeMins;

    }

    /**
     * @return \Shell\Messages
     */
    static public function message()
    {
        if (!self::$_messages) {
            self::$_messages = new \Shell\Messages(self::getLogFile(), self::isInteractive(), self::$_alertMail, self::$_verbosity,self::isKubernetes());
        }
        return self::$_messages;
    }

    static public function alert($msg, $title = '')
    {
        return self::message()->error($msg);
    }

    static public function debug($msg)
    {
        return self::message()->debug($msg);
    }

    static public function warning($msg)
    {
        return self::message()->warning($msg);
    }

    static public function info($message)
    {
        return self::message()->info($message);
    }

    static public function error($msg, $sendAlert = true)
    {
        return self::message()->error($msg);
    }

    static public function msg($message, $color = [], $eol = true)
    {
        self::message()->msg($message, $color, $eol);
    }

    static public function exception(\Exception $E)
    {
        self::alert("Exception : " . $E->getMessage());
        exit(2);
    }

    static private function makePidAndLogFile($call_class_name)
    {
        if (self::isKubernetes()) return false;
        $ext_pid = '';
        if (sizeof(self::$_pidListCommands)) {
            foreach (self::$_pidListCommands as $key => $method) {
                if (self::get((is_array($method) ? $key : $method)))
                    $ext_pid .= '_' . (is_array($method) ? $key : $method) . '-' . self::get((is_array($method) ? $key : $method), null);
            }
        }
        $path = self::$_pidFilePath;
        if (!$path) {
            $path = sys_get_temp_dir();
        } else {
            $path = rtrim($path, DIRECTORY_SEPARATOR);
        }

        if (!is_dir($path)) {
            throw new \Shell\ShellException("is not dir : " . $path);
        }

        $prefix = "";
        if (strlen(self::$_pidFilePrefix)) {
            $prefix = strval(self::$_pidFilePrefix);
        }

        $pid = [];
        $pid[] = '_lock_';
        $pid[] = self::getName().'_';
        $pid[] = $prefix;
        $pid[] = $call_class_name;
        if ($ext_pid) $pid[] = preg_replace("/[^A-Za-z0-9_-]+/", '', $ext_pid);
        self::$_pid = $path . DIRECTORY_SEPARATOR . implode('', $pid) . '.pid.tmp';


        // ------------------ LOG FILE


        if (!self::$_logFilePrefix)
        {
            self::$_logFilePrefix=self::getName();
        }

        if (!self::$_logFilePath)
        {
            self::$_logFilePath=sys_get_temp_dir();
        }
        $path=rtrim(self::$_logFilePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::$_logFilePrefix;
        // log file
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!is_dir($path)) {
            throw new \Shell\ShellException("is not dir : " . $path);
        }
        $pid = [];
        $pid[] = @date('Y-m-d').'_';
        $pid[] = $prefix;
        $pid[] = $call_class_name;
        if ($ext_pid) $pid[] = preg_replace("/[^A-Za-z0-9_-]+/", '', $ext_pid);

        self::$_logFile = $path . DIRECTORY_SEPARATOR . implode('', $pid) . '.log';

        return true;
    }

    private static function processExists($pid)
    {
        if (function_exists('posix_getsid')) {
            $sid = posix_getsid($pid);
            if ($sid == false) {
                return false;
            }
        }

        return true;
    }

    private static function checkShell()
    {
        if (self::isKubernetes()) return false;
        $f = self::getPidFileName();

        clearstatcache(true, $f);// drop cache

        $m = @filemtime($f);
        if ($m === false) return false;
        // -----------------------------------
        $pid = file_get_contents($f);
        if ($pid === FALSE) return false;

        $is = self::processExists($pid);

        if ($is == false) {
            self::warning("!Process not exist,not find by pid!\n");
            unlink($f);
            self::stopShell();
            return false;
        }

        // ---------------------------------------------------------------------------------------------------------
        $diff = round((time() - $m) / 60, 1);
        if (self::$_maxTimeMins > 0 && $diff > self::$_maxTimeMins) {
            self::warning("! Long Process PID : $pid, Running times: $diff minutes ago , try kill ....");

            if (posix_kill($pid, 9)) {
                self::debug("> posix kill : $pid say ok ");
                $sid = posix_getsid($pid);
                self::debug("> get sid : result : " . intval($sid) . " for pid : $pid");
                // ---------------------------------------------
                if ($sid < 1) {
                    self::error("!! Kill OK !! [$f]");
                    unlink($f);
                    exit(2);
                } else {
                    self::error("WFT? try restart ? [$f]");
                }
            } else {
                self::error("Posix cant kill [$f]");
            }

            return true;
        } else {
            if (!self::isInteractive())
            {
                self::debug("> Process PID : $pid, Running times: $diff minutes ago");
                self::info("> process exits, exit...");
            }

        }


        return true;
    }


    static private function isICanRun()
    {
        if (self::$_init) return true;

        // @todo normalazie messages
        if (Shell::checkShell()) {
            $f_exit = true;
            if (self::get('wait')) {
                self::warning("Can`t run pid exists : " . self::getPidFileName() . " , try wait.");
                for ($f = 0; $f < 500; $f++) {
                    sleep(1);
                    if (!Shell::checkShell()) {
                        $f_exit = false;
                        self::warning("PID is free... run");
                        break;
                    }
                }
            }
            if ($f_exit) {
                if (!self::isInteractive()) {
                    self::error("ShellPID:Can`t run pid exists  : " . self::getPidFileName() . "\n");
                }
                exit(self::$_exitCodeOnPidExists);
            }
        }
        register_shutdown_function('Shell::stopShell');
        self::startShell();
        self::$_init = true;
        return true;
    }

    static private function argumentsInits()
    {
        if (!self::$_isArgInits) {

            $argv = $GLOBALS['argv'];

            array_shift($argv);
            $o = array();
            foreach ($argv as $a) {
                if (substr($a, 0, 2) == '--') {
                    $eq = strpos($a, '=');
                    if ($eq !== false) {
                        $o[substr($a, 2, $eq - 2)] = substr($a, $eq + 1);
                    } else {
                        $k = substr($a, 2);
                        if (!isset($o[$k])) {
                            $o[$k] = true;
                        }
                    }
                } else if (substr($a, 0, 1) == '-') {
                    if (substr($a, 2, 1) == '=') {
                        $o[substr($a, 1, 1)] = substr($a, 3);
                    } else {
                        foreach (str_split(substr($a, 1)) as $k) {
                            if (!isset($o[$k])) {
                                $o[$k] = true;
                            } else {
                                if (is_bool($o[$k])) {
                                    $o[$k] = 2;
                                } else {
                                    $o[$k]++;
                                }
                            }
                        }
                    }
                } else {
                    $o[$a] = true;
                }
            }
            self::$_arg = $o;
            self::$_isArgInits = true;

        }
        return true;
    }

    static public function getAll()
    {
        self::argumentsInits();
        return self::$_arg;

    }

    static public function get($name, $ifNot = null)
    {
        self::argumentsInits();
        $name = ltrim($name, '--');
        $result = $ifNot;
        if (isset(self::$_arg[$name])) {
            $result = self::$_arg[$name];
        }
        return $result;
    }

    private static function startShell()
    {
        if (!self::isKubernetes()) {
            if (!file_put_contents(self::getPidFileName(), getmypid())) {
                throw new \Shell\ShellException('error : Shell , cant file_put_contents ! in ' . self::getPidFileName());
            }
        }
        self::$_isMakePid = true;
    }

    public static function stopShell()
    {
        if (self::$_isMakePid) {
            if (!self::isKubernetes())
                @unlink(self::getPidFileName());
        }
    }

    static public function getPidFileName()
    {
        return self::$_pid;
    }

    static private function getClassFunctions($object, $reg = 'Command')
    {
        $out = array();
        $reflector = new ReflectionClass($object);
        $r = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($r as $p) {

            if (stripos($p->name, $reg) === false) continue;
            //Get the parameters of a method
            $d = str_ireplace($reg, '', $p->name);
            $out[$d] = array();
            $parameters = $reflector->getMethod($p->name)->getParameters();
            $docComment = $reflector->getMethod($p->name)->getDocComment();


            $doc=[];
            $docTitle='';
            if ($docComment)
            {
                $docTitle=trim(str_replace(array('/', '*'), '', substr($docComment, 0, strpos($docComment, '@'))));
                $docTitle=str_replace('   ','',$docTitle);

                if (preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $docComment, $matches,PREG_SET_ORDER)){
                    foreach ($matches as $mat)
                    {
                        if (strtolower($mat[1])=='param')
                        {

                            $line=$mat[2];
                            $matparams=[];
                            if (preg_match_all('/(?P<type>\w+)\s+\$(?P<key>\w+)\s(?P<text>.*)/m',$line,$matparams,PREG_SET_ORDER))
                            {
                                foreach ($matparams as $k)
                                {
                                    $doc['param'][$k['key']]=['type'=>$k['type'],'text'=>$k['text']];
                                }
                            }

                        }
                        else
                        {
                            $doc[$mat[1]][]=$mat[2];
                        }

                    }
                }
            }
            $out[$d]['doc']=$doc;
            $out[$d]['docTitle']=$docTitle;

            foreach ($parameters as $param) {
                $out[$d]['params'][] = array('name' => $param->name, 'isOptional' => $param->isOptional());
            }
        }
        return $out;
    }

    static public function setExitCodeOnPidExists($code)
    {
        self::$_exitCodeOnPidExists=intval($code);
    }

    static public function run($class)
    {

        if (!self::getName()) {
            throw new \Shell\ShellException("Name must be set");
        }
        if (!is_object($class)) {
            throw new \Shell\ShellException("Class must be is_object");
        }



        self::makePidAndLogFile(get_class($class));
        self::initVerbosity();

        // ------------------------------------------------------------------------
        if (self::get('help') || self::get('h'))
        {
            return self::renderHelp($class);
        }
        try
        {
            \Shell::message()->setEnvironment(\Shell::getEnvironment());
            \Shell::message()->setAppName(self::$_name);
            \Shell::message()->setCallMethod(get_class($class));

            self::isICanRun();

            foreach (self::getAll() as $paramName => $value) {
                $functName = 'set' . ucwords($paramName);
                if (method_exists($class, $functName)) {
                    call_user_func_array(array($class, $functName), array($value));
                }
            }

            return self::callableClass($class);

        }
        catch (Exception $E)
        {
            self::exception($E);
            exit(2);
        }
        // ------------------------------------------------------------------------
    }

    static public function getVerbosity()
    {
        return self::$_verbosity;
    }

    static private function initVerbosity()
    {
        if (self::get('q')) {
            self::$_verbosity = self::VERBOSITY_QUIET;
        }

        $level = intval(self::get('v', 0));
        if ($level) {
            switch ($level) {
                case 1:
                    self::$_verbosity = self::VERBOSITY_NORMAL;
                    break;
                case 2:
                    self::$_verbosity = self::VERBOSITY_INFO;
                    break;
                case 3:
                    self::$_verbosity = self::VERBOSITY_DEBUG;
                    break;
                default:
                    self::$_verbosity = self::VERBOSITY_DEBUG;
                    break;
            }
        }
    }

    static private function renderHelp($class)
    {
        $title='';

        if (method_exists($class,'getTitle'))
        {
            $title=trim($class->getTitle());
        }

        $help="";
        $help.='<bg_light_blue> '.self::getName().' </bg_light_blue>';


        if (method_exists($class,'getTitle'))
        {

            $help.="\n".str_repeat('---',10)."\n";
            $help.=$title."\n".str_repeat('---',10)."\n";

        }


        $lists='';
        $listParamsForMethod = self::getClassFunctions($class);
        foreach ($listParamsForMethod as $method => $data) {

                $doc=$data['doc'];
                $title=$data['docTitle'];$title=str_ireplace(["\n","\r"],' ',$title);



                $lists.='> <light_yellow>'.$method."</light_yellow>\t\t -- $title\n";



                if (isset($data['params']) && sizeof($data['params'])) {
                    foreach ($data['params'] as $param) {
                        $paramName = $param['name'];
                        $docparams='';
                        if (isset($doc['param'][$paramName]))
                        {
                            $docparams=implode(',',$doc['param'][$paramName]);

                        }
                        if ($param['isOptional'] != true) {
                            $lists.="\t\t\t".'<green>--'.$paramName.'</green>'." <dark_gray>$docparams</dark_gray>\n";
                        }
                        else
                        {
                            $lists.="\t\t\t".'<dark_gray>[--'.$paramName.']</dark_gray>'." <dark_gray>$docparams</dark_gray>\n";
                        }
                    }
                }
                $lists.="\n";
        }//$listParamsForMethod

        $help.="\n".str_repeat('---',10);
        $help.="\n$lists\n\n\n";

        self::message()->msg($help,[],false,false);
        exit;
    }
    static private function callableClass($class)
    {
        $resultOut = '';
        //auto create methods, get all "xyzCommand" functions -> --xyz
        $listParamsForMethod = self::getClassFunctions($class);
        foreach ($listParamsForMethod as $method => $data) {
            if (self::get($method)) {
                $name = $method . 'Command';
                $p = array();

                if (isset($data['params']) && sizeof($data['params'])) {
                    $params=$data['params'];
                    foreach ($params as $param) {
                        $paramName = $param['name'];
                        $value = self::get($paramName, null);
                        if ($value === null && $param['isOptional'] != true) {
                            throw new \Shell\ShellException("Can`t call: " . get_class($class) . "->$name() with empty param : " . $paramName);
                        }
                        $p[$paramName] = $value;
                        if ($paramName === $method) $p[$method] = true;
                    }
                }

                \Shell::message()->setCallMethod(get_class($class).'\\'.$method);

                $result = call_user_func_array(array($class, $name), $p);


                if (!is_bool($result))
                {
                    self::msg($result);
                }

            }
        }
        // ------------------------------------------------------------------------
        return $resultOut;
    }


}