## Php cli helper


See other 

https://github.com/nramenta/clio/blob/master/README.md

https://github.com/c9s/CLIFramework/blob/master/README.md

https://github.com/wp-cli/php-cli-tools

https://raw.githubusercontent.com/dealnews/Console/master/src/Console.php

https://github.com/nramenta/clio

https://github.com/thephpleague/climate


## Install

```
composer require isublimity/shell

```

## Use case

Есть класс который нам нужно вызвать из консоли 
```php


class xyzActions
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


```


Подключаем обертку: 

```php
\Shell::name("xyz");
\Shell::run(
         new xyzActions()
     );

```


Получаем help:

```bash

> php test.php help
 xyz
------------------------------
> list		 -- Получить список бла-бла
			--name string,Назврание
			[--reg] bool,включить или выключить


```


Запускаем : 
```bash

> php test.php list
Exception : Can`t call: xyzActions->listCommand() with empty param : name


> php test.php list --name=bob
My name bob


> php test.php list --name=bob --reg
My name bob  ;)



> php test.php --list --name=bob
My name bob
```


### Системные параметры
```
-v [-vv | -vvv] 
--wait
--help 
-q 

```

Цвета : 

```php

Shell::msg("ABC <light_blue> FGHJ </light_blue> Command();");

Shell::msg("message");

Shell::debug("DEBUG!");

Shell::info("INFO!");

Shell::warning("WARN!");

Shell::error("ERORR!!");

```

#### Заголовок для help

```php
class xyzActions
{
    public function getTitle()
    {
        return 'ABOUTE xyzActions - <red>XYZ</red>';
    }
}
```


#### Setters 

```php
class xyzActions
{
    public function setColor($value)
    {
        echo "CALL setColor($value)\n";
    }
}

Автоматически вызовется если : > php test.php --color=YXA


```

### PID 

```php
// Блокирует исполнение 
Shell::maxExecutionMinutes(0.5);//30 seconds 


Shell::dir(__DIR__); // сменить директорию 
Shell::alertMail('igor@x.x'); // куда отпраить ошибки
Shell::setPathLog("/tmp/");   // куда записывать все сообщения 
Shell::setPathPid("/tmp/");   // где PID 
Shell::setPidCommands(array('check','test')); // какие комманды можно запускать одновренменно 

```










![](https://api.monosnap.com/rpc/file/download?id=rBvPAlUQsLJJUXDkS9Sd3PKlMTeN5g)