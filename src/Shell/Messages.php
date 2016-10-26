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
    public function msg($message, $styles, $eol = true, $timepoints=true)
    {

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
                $message = $this->color()->apply($k, $message);

            }
        }
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
        echo trim($message).($eol?PHP_EOL:"");
        flush();
        $this->storeFile($message);
        return $message;

    }


}