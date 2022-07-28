<?php


namespace System\Host
{


    use Exception;

    interface IStartUp
    {
        /**
         * Run StartUp class
         * @throws Exception
         */
        public function run(): void;
    }
}