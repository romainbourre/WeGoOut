<?php

namespace Domain\Entities
{

    use DateTime;

    /**
     * Represent a datetime
     * Class Date
     * @package App\Lib
     * @author Bourré Romain
     */
    class Date
    {

        private $timestamp;

        /**
         * Date constructor.
         * @param int $timestamp timestamp
         */
        public function __construct(int $timestamp)
        {
            if (!is_null($timestamp) && !empty($timestamp))
            {
                $this->timestamp = $timestamp;
            }
        }

        /**
         * Get a timestamp of date
         * @return int
         */
        public function getTimeStamp(): int
        {
            return $this->timestamp;
        }

        /**
         * Get a complete french day of the date
         * @param string|null $format date format
         * @return string french day
         */
        public function getFrenchDay(?string $format = null): ?string
        {

            $short = false;
            if ($format == "dddd") $short = false;
            else if ($format == "dd") $short = true;

            switch (date("N", $this->timestamp))
            {

                case 1:
                    if ($short) return "Lun.";
                    else return "Lundi";
                    break;
                case 2:
                    if ($short) return "Mar.";
                    else return "Mardi";
                    break;
                case 3:
                    if ($short) return "Mer.";
                    else return "Mercredi";
                    break;
                case 4:
                    if ($short) return "Jeu.";
                    else return "Jeudi";
                    break;
                case 5:
                    if ($short) return "Ven.";
                    else return "Vendredi";
                    break;
                case 6:
                    if ($short) return "Sam.";
                    else return "Samedi";
                    break;
                case 7:
                    if ($short) return "Dim.";
                    else return "Dimanche";
                    break;

            }

            return null;

        }

        /**
         * Get a complete french month of  the date
         * @param string $format month format
         * @return null|string french month
         */
        public function getFrenchMonth(?string $format = null): ?string
        {

            $short = false;
            if ($format == "mmmm") $short = false;
            else if ($format == "mm") $short = true;

            switch (date("n", $this->timestamp))
            {

                case 1:
                    if ($short) return "Jan.";
                    else return "Janvier";
                    break;
                case 2:
                    if ($short) return "Fév.";
                    else return "Février";
                    break;
                case 3:
                    if ($short) return "Mar.";
                    else return "Mars";
                    break;
                case 4:
                    if ($short) return "Avr.";
                    else return "Avril";
                    break;
                case 5:
                    return "Mai";
                    break;
                case 6:
                    return "Juin";
                    break;
                case 7:
                    if ($short) return "Juil.";
                    else return "Juillet";
                    break;
                case 8:
                    return "Août";
                    break;
                case 9:
                    if ($short) return "Sep.";
                    else return "Septembre";
                    break;
                case 10:
                    if ($short) return "Oct.";
                    else return "Octobre";
                    break;
                case 11:
                    if ($short) return "Nov.";
                    else return "Novembre";
                    break;
                case 12:
                    if ($short) return "Déc.";
                    else return "Décembre";
                    break;

            }

            return null;

        }

        /**
         * Get complete french date
         * @param string $format date format
         * @return string french date
         */
        public function getFrenchDate(?string $format = null): string
        {

            preg_match("#d+#", $format, $day);
            if (isset($day[0])) $day = $day[0];
            else $day = null;
            preg_match("#m+#", $format, $month);
            if (isset($month[0])) $month = $month[0];
            else $month = null;

            return $this->getFrenchDay($day) . " " . date("d", $this->timestamp) . " " . $this->getFrenchMonth($month) . " " . date("Y", $this->timestamp);

        }

        /**
         * Get a smart date and time
         * @return string date and time
         */
        public function getFrenchSmartDate(): string
        {

            $hoursDisplay = 12;

            $dateNow = new DateTime();
            $date = (new DateTime())->setTimestamp($this->timestamp);
            $interval = $dateNow->diff($date);
            $hours = $interval->h;
            $minutes = $interval->i;

            if ($hours <= $hoursDisplay && date("d/m/Y", $this->timestamp) == date("d/m/Y"))
            {

                if ($hours < 1)
                {
                    if ($minutes < 1)
                    {
                        return "à l'instant";
                    }
                    else if ($minutes == 1)
                    {
                        return "Il y a " . $minutes . " minute";
                    }
                    else
                    {
                        return "Il y a " . $minutes . " minutes";
                    }
                }
                else if ($hours == 1)
                {
                    return "Il y a " . $hours . " heure";
                }
                else if ($hours > 1)
                {
                    return "Il y a " . $hours . " heures";
                }

            }
            else if ($hours > $hoursDisplay && date("d/m/Y", $this->timestamp) == date("d/m/Y"))
            {

                return "Aujourd'hui à " . date("H:i", $this->timestamp);

            }


            return date("d", $this->timestamp) . " Date.php" . $this->getFrenchMonth() . ", " . date("H:i", $this->timestamp);

        }
    }
}