<?php

include_once __DIR__.'/../include.php';

class sleepsActions
{
    /**
     * Заснуть на время
     *
     * @param int $seconds Секунды
     * @return array
     */
    public function sleepCommand($seconds)
    {
        Shell::msg("CALL <light_blue>sleep</light_blue> Command();");
        for ($f=0;$f<$seconds;$f++)
        {
            sleep(1);
            echo "Sleep....".($seconds-$f)."      \r";
        }
        Shell::warning("Exit sleep");
    }
}


Shell::name("sleep");
Shell::dir(__DIR__);
Shell::maxExecutionMinutes(0.5);//30 seconds

Shell::run(
    new sleepsActions()
);



/*

php example/02_pids.php help

php example/02_pids.php sleep --seconds=123

php example/02_pids.php sleep --seconds=13 -vvv --wait

*/