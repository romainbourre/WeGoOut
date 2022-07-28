<?php

namespace System\Librairies
{

    /**
     * Class Security
     * Security methods
     * @package Core\Ext
     */
    class Security
    {

        /**
         * Generate random string
         * @param int $nbrCharacter number of character
         * @param int $typeString complexity level
         * @return string random string
         */
        public static function generateRandomString(int $characterNumber, int $typeString): string
        {
            if ($typeString == '1')
            {
                $caract = "0123456789";
            }
            else if ($typeString == '2')
            {
                $caract = "abcdefghijklmnopqrstuvwyxzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            }
            else if ($typeString == '3')
            {
                $caract = "abcdefghijklmnopqrstuvwyxzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            }
            else if ($typeString == '4')
            {
                $caract = "abcdefghijklmnopqrstuvwyxzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@!:;,§/?*µ$=+";
            }

            $finalPassword = "";
            for ($i = 1; $i <= $characterNumber; $i++)
            {

                // On compte le nombre de caractères
                $Nbr = strlen($caract);

                // On choisit un caractère au hasard dans la chaine sélectionnée :
                $Nbr = mt_rand(0, ($Nbr - 1));

                // Pour finir, on écrit le résultat :
                $finalPassword = $finalPassword . $caract[$Nbr];

            }

            return $finalPassword;


        }

        /**
         * @return string génère une chaîne MD5 basé sur le TimeStamp
         */
        public static function generateTimeStampMd5(): string
        {
            return md5(microtime());
        }
    }
}