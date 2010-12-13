<?php

    /**
     * The Hello World class!
     *
     * @author Michiel Rook
     * @version $Id: HelloWorld.php 592 2009-10-04 21:37:22Z mrook $
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
