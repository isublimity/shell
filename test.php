<?php
//https://raw.githubusercontent.com/dealnews/Console/master/src/Console.php
//https://github.com/nramenta/clio
// https://github.com/thephpleague/climate
class xyzActions
{
    public function setColor($value)
    {
        echo "CALL setColor($value)\n";
    }
    public function setLimit($value)
    {
        echo "CALL setLimit($value)\n";
    }
    public function listCommand($key,$value=false)
    {
        echo "CALL listAction(key=$key,value=$value)\n";
    }

    public function sleepCommand($seconds=20)
    {

        for ($f=0;$f<$seconds;$f++)
        {
            sleep(1);
            echo "Sleep,....".($seconds-$f)."\r";
        }
        echo "\nExit\n";
    }
    public function pokeCommand()
    {
        echo "CALL pokeCommand();\n";
    }


    public function abcCommand()
    {
        echo "CALL abcCommand();\n";
    }
}
include 'src/Shell.php';
include 'src/Shell/Color.php';
include 'src/Shell/Messages.php';
include 'src/Shell/ShellException.php';
try
{
    Shell::name("ModelsCH");
    Shell::alertMail('na@garika.net');
    Shell::setLogFile("/tmp/",'models');
    Shell::setPathPid("/tmp/");
    Shell::setPidCommands(array('state','test'));
    // -------------------------------------------------------
    Shell::maxExecutionTime(10);
    // -------------------------------------------------------
    Shell::run(
        new xyzActions()
    );

    Shell::info("INFO!");
    Shell::warning("WARN!");
    Shell::error("ERORR!!");
    // -------------------------------------------------------
    exit(0);
}
catch (Exception $E)
{
    var_dump($E->getMessage());
    die("\n!!!\n");
    Shell::exception($E);
    exit(2);
}