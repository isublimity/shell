<?php
namespace Shell;
class Messages
{

    const WARN = 'warning';
    const INFO = 'info';
    const ERROR = 'error';

    private $isInteractive = true;
    private $isKubernetes = true;
    private $logFile = '';
    private $verbosity = 0;
    private $alertMail = '';
    private $appName = '';
    private $methodName = '';
    private $environment = '';
    protected $wrapped = '';
    private $_color;


    public function __construct($logFile = '', $isInteractive = '', $alertMail = '', $verbosity_level = 0,$isKubernetes=false)
    {

        $this->verbosity = $verbosity_level;
        $this->isInteractive = $isInteractive;
        $this->logFile = $logFile;
        $this->alertMail = $alertMail;
        $this->_color = new Color();
        $this->isKubernetes = $isKubernetes;
        $this->appName = 'app';
        $this->environment = 'dev';
        $this->methodName = '';
    }

    public function setCallMethod($m)
    {
        $this->methodName=str_replace('\\','-',$m);
    }
    public function setEnvironment($environment)
    {
        if ($environment) {
            $this->environment = $environment;
        }

    }
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * @return Color
     */
    public function color()
    {
        return $this->_color;
    }

    public function message($msg, $styles, $eol = true)
    {
        return $this->msg($msg, $styles, $eol);
    }

    private function storeFile($msg)
    {
        if (!file_put_contents($this->logFile,trim($msg)."\n",FILE_APPEND))
        {
            throw new \Exception("Can`t store to file");
        }
    }

    public function debug($message)
    {
        if ($this->verbosity<\Shell::VERBOSITY_DEBUG) return false;
        return $this->msg($message,[\Shell::dark_gray,\Shell::italic]);
    }

    public function warning($message)
    {
        return $this->msg($message,[\Shell::yellow],true,true,self::WARN);
    }
    public function error($message)
    {
        return $this->msg($message,[\Shell::white,\Shell::bg_red,\Shell::bold],true,true,self::ERROR);
    }
    public function info($message)
    {
        if ($this->verbosity<\Shell::VERBOSITY_INFO) return false;
        return $this->msg($message,[\Shell::cyan]);
    }
    public function colorize($message)
    {
        $message=$this->color()->colorize($message);
        $this->color()->reset();
        return $message;
    }
    public function msg($message, $styles = 0, $eol = true, $timepoints=true,$level=self::INFO)
    {

        if (!(is_string($message) || is_numeric($message)) )
        {
            if ($this->isKubernetes) {
                $message=json_encode($message,JSON_UNESCAPED_UNICODE);
            } else {
                $message=json_encode($message,JSON_PRETTY_PRINT);
            }

        }

        // ------------------------------------------------

        if ($this->isKubernetes) {

            echo json_encode(
                    [
                        'timestamp'=>date('Y-m-d H:i:s T'),
                        'message'=>$message,
                        'verbose'=>intval($this->verbosity),
                        'environment'=>$this->environment,
                        'log_level'=>$level,
                        'app'=>$this->appName,    // syncer,loader...
                        'method'=>$this->methodName, // [update,insert...]

                    ],
                    JSON_UNESCAPED_UNICODE
                )."\n";
            return true;
        }


        if (is_int($styles) && $styles > 1) {
            $xc = $styles;
            $styles = [];

            if ($xc == 1) $styles = [\Shell::cyan];
            elseif ($xc == 2) $styles = [\Shell::red];
            elseif ($xc == 3) $styles = [\Shell::blue];
            elseif ($xc == 4) $styles = [\Shell::green];
            elseif ($xc == 5) $styles = [\Shell::gray];
        }

        if (is_string($styles)) {
            $styles = [$styles];
        }

        $this->color()->reset();

        if (is_array($styles) && sizeof($styles)) {

            $message = trim($message);
            foreach ($styles as $k) {
                $message='<'.$k.'>'.$message.'</'.$k.'>';
            }

        }
        $message=$this->color()->colorize($message);
        $this->color()->reset();

        // ------------------------------------------------
        if ($timepoints)
        {
            if ($this->isInteractive)
            {
                $message=$this->color()->apply(\Shell::dark_gray,@date('H:i:s')).': '.$message;
            }
            else
            {
                $message=@date('H:i:s').': '.$message;

            }

        }
        if ($eol) $message=trim($message);

        echo $message.($eol?PHP_EOL:"");
        flush();

        if (!$this->isKubernetes) {
            $this->storeFile($message);
        }
        return true;

    }

    public function textTable($data,$echo=true)
    {
        $renderer = new \MathieuViossat\Util\ArrayToTextTable($data);
        $txt=$renderer->getTable();
        if ($echo) {echo $txt;return '';}
        return $txt;
    }
    /**
     * Prompts the user for input. Optionally masking it.
     *
     * @param   string  $prompt     The prompt to show the user
     * @param   bool    $masked     If true, the users input will not be shown. e.g. password input
     * @param   int     $limit      The maximum amount of input to accept
     * @return  string
     */
    public function prompt($prompt, $masked=false, $limit=100)
    {
        echo "$prompt: ";



        if ($masked) {
            `stty -echo`; // disable shell echo
        }
        $buffer = "";
        $char = "";
        $f = fopen('php://stdin', 'r');
        while (strlen($buffer) < $limit) {
            $char = fread($f, 1);
            if ($char == "\n" || $char == "\r") {
                break;
            }
            $buffer.= $char;
        }
        if ($masked) {
            `stty echo`; // enable shell echo
            echo "\n";
        }
        return $buffer;
    }

    /**
     * Prompts the user with a yes/no question.
     *
     * @param   string  $prompt     The prompt to show the user
     * @return  bool
     */
    public function confirm($prompt)
    {
        $answer = false;

        if (strtolower($this->prompt($prompt." [y/N]", false, 1)) == "y") {
            $answer = true;
        }

        return $answer;

    }


}