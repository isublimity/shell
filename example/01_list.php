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

        \Shell::message()->msg("My name $name ");
        if ($reg) \Shell::message()->msg( " ;) ");
        throw new Exception('ERROR !!ZASD');
        return 'OK!';
    }
}


Shell::name("aggrSyncer");
Shell::run(
    new listActions()
);
