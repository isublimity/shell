<?php

class xyzActions
{
//    public function getTitle()
//    {
//        return '
//        ABOUTE xyzActions - <red>XYZ</red>
//        ';
//    }
//
//    public function setColor($value)
//    {
//        echo "CALL setColor($value)\n";
//    }
//    public function setLimit($value)
//    {
//        echo "CALL setLimit($value)\n";
//    }

    /**
     * Получить список бла-бла
     *
     * @param string $name Назврание
     * @param bool $reg включить или выключить
     * @return array
     */
    public function listCommand($name,$reg=false)
    {

        echo "My name $name ";
        if ($reg) echo " ;) ";
        echo "\n";

//        Shell::msg("CALL <light_blue>LIST</light_blue> Command(name=[<red>$name</red>] , reg=[$reg] );");
//        return ['a'=>1,'b'=>2];
    }

//    /**
//     * Sleep some time
//     * If sleep use pid for ....
//     * Make meet happy
//     *
//     * @param int $seconds кол-во секкунд
//     * @param int $secondSeconds Что то там и как то там, кол-во чегото
//     * @return bool
//     */
//    public function sleepCommand($seconds=20,$secondSeconds=132)
//    {
//        Shell::msg("CALL <light_blue>sleep</light_blue> Command();");
//        for ($f=0;$f<$seconds;$f++)
//        {
//            sleep(1);
//            echo "Sleep....".($seconds-$f)."      \r";
//        }
//        Shell::warning("Exit sleep");
//    }
//    public function abcdCommand()
//    {
//        Shell::msg("CALL <light_blue>abcD</light_blue> Command();");
//        return 'result=false';
//    }
//
//
//    public function abcCommand()
//    {
//        Shell::msg("CALL <light_blue>ABC</light_blue> Command();");
//    }
}
include 'src/Shell.php';
include 'src/Shell/Color.php';
include 'src/Shell/Messages.php';
include 'src/Shell/ShellException.php';
try
{
//    Shell::dir(__DIR__);
    Shell::name("xyz");
//    Shell::alertMail('na@garika.net');
//    Shell::setPathLog("/tmp/");
//    Shell::setPathPid("/tmp/");
//    Shell::setPidCommands(array('list','test'));
    // -------------------------------------------------------
//    Shell::maxExecutionMinutes(12);
    // -------------------------------------------------------
    Shell::run(
        new xyzActions()
    );


    Shell::msg("LOG:".Shell::getLogFile());
    Shell::msg("PID:".Shell::getPidFileName());
    Shell::msg("isInteractive:".Shell::isInteractive());
    Shell::msg("message");
    Shell::debug("DEBUG!");
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