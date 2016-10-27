<?php

include_once __DIR__.'/../include.php';

class listActions
{
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
    }
}


Shell::name("list");
Shell::run(
    new listActions()
);
