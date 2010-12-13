<?php

    /**
     * The Hello World class!
     *
     * @author Michiel Rook
     * @version $Id: HelloWorld.php 552 2009-08-29 12:18:13Z mrook $
     * @package hello.world
     */
    class HelloWorld
    {
        public function foo($silent = true)
        {
            if ($silent) {
                return;
            }
            return 'foo';
        }

        function sayHello()
        {
            return "Hello World!";
        }
    };

?>
