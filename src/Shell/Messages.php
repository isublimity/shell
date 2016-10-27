<?php
namespace Shell;
class Messages
{

    private $isInteractive = true;
    private $logFile = '';
    private $verbosity = 0;
    private $alertMail = '';
    protected $wrapped = '';
    private $_color;


    public function __construct($logFile = '', $isInteractive = '', $alertMail = '', $verbosity_level = 0)
    {

        $this->verbosity = $verbosity_level;
        $this->isInteractive = $isInteractive;
        $this->logFile = $logFile;
        $this->alertMail = $alertMail;
        $this->_color = new Color();
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
        return $this->msg($message,[\Shell::yellow]);
    }
    public function error($message,$sendAlert=true)
    {
        return $this->msg($message,[\Shell::white,\Shell::bg_red,\Shell::bold]);
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
    public function msg($message, $styles, $eol = true, $timepoints=true)
    {

        if (!(is_string($message) || is_numeric($message)) )
        {
            $message=json_encode($message,JSON_PRETTY_PRINT);
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
        $this->storeFile($message);
        return true;

    }

    /**
     * Prompts the user for input. Optionally masking it.
     *
     * @param   string  $prompt     The prompt to show the user
     * @param   bool    $masked     If true, the users input will not be shown. e.g. password input
     * @param   int     $limit      The maximum amount of input to accept
     * @return  string
     */
    public static function prompt($prompt, $masked=false, $limit=100)
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
    public static function confirm($prompt)
    {
        $answer = false;

        if (strtolower(self::prompt($prompt." [y/N]", false, 1)) == "y") {
            $answer = true;
        }

        return $answer;

    }


}