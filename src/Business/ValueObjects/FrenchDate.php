<?php

namespace Business\ValueObjects
{

    use Business\Exceptions\IncorrectDateFormatException;
    use Business\Exceptions\IncorrectDateIndexException;
    use Business\Exceptions\ValidationException;
    use DateTime;
    use Exception;


    class FrenchDate
    {
        public const SHORT_FORMAT = "dd";
        public const LONG_FORMAT = "dddd";

        public readonly DateTime $value;


        /**
         * @throws Exception
         */
        public function __construct(private readonly int $timestamp)
        {
            $this->value = new DateTime();
            $this->value->setTimestamp($this->timestamp);
        }

        /**
         * @throws IncorrectDateFormatException
         * @throws IncorrectDateIndexException
         */
        public function getDayOfWeek(string $format = self::LONG_FORMAT): string
        {
            if ($format != self::SHORT_FORMAT && $format != self::LONG_FORMAT) {
                throw new IncorrectDateFormatException($format);
            }

            $isShortDayFormat = $format == self::SHORT_FORMAT;
            $numberOfWeekDay = date("N", $this->timestamp);
            $dayOfWeekText = match ($numberOfWeekDay) {
                '1' => ['Lun.', 'Lundi'],
                '2' => ['Mar.', 'Mardi'],
                '3' => ['Mer.', 'Mercredi'],
                '4' => ['Jeu.', 'Jeudi'],
                '5' => ['Ven.', 'Vendredi'],
                '6' => ['Sam.', 'Samedi'],
                '7' => ['Dim.', 'Dimanche'],
                default => throw new IncorrectDateIndexException($numberOfWeekDay)
            };
            return $isShortDayFormat ? $dayOfWeekText[0] : $dayOfWeekText[1];
        }

        /**
         * @throws IncorrectDateFormatException
         * @throws IncorrectDateIndexException
         */
        public function getMonth(string $format = self::LONG_FORMAT): string
        {
            if ($format != self::SHORT_FORMAT && $format != self::LONG_FORMAT) {
                throw new IncorrectDateFormatException($format);
            }

            $isShortMonthFormat = $format == self::SHORT_FORMAT;
            $numberOfMonth = date("n", $this->timestamp);
            $monthText = match ($numberOfMonth) {
                '1' => ['Jan.', 'Janvier'],
                '2' => ['Fév.', 'Février'],
                '3' => ['Mar.', 'Mars'],
                '4' => ['Avr.', 'Avril'],
                '5' => ['Mai', 'Mai'],
                '6' => ['Juin', 'Juin'],
                '7' => ['Juil.', 'Juillet'],
                '8' => ['Août', 'Août'],
                '9' => ['Sep.', 'Septembre'],
                '10' => ['Oct.', 'Octobre'],
                '11' => ['Nov.', 'Novembre'],
                '12' => ['Déc.', 'Décembre'],
                default => throw new IncorrectDateIndexException($numberOfMonth)
            };
            return $isShortMonthFormat ? $monthText[0] : $monthText[1];
        }

        /**
         * @throws IncorrectDateFormatException
         * @throws IncorrectDateIndexException
         */
        public function getDate(string $format = self::LONG_FORMAT): string
        {
            $dayOfWeekText = $this->getDayOfWeek($format);
            $dayText = date("d", $this->timestamp);
            $monthText = $this->getMonth($format);
            $yearText = date("Y", $this->timestamp);
            return "$dayOfWeekText $dayText $monthText $yearText";
        }

        /**
         * @throws IncorrectDateFormatException
         * @throws IncorrectDateIndexException
         */
        public function getRelativeDateAndHours(): string
        {
            $maximumHoursDisplayed = 12;
            $dateNow = new DateTime();
            $date = (new DateTime())->setTimestamp($this->timestamp);
            $interval = $dateNow->diff($date);
            $elapsedHours = $interval->h;
            $elapsedMinutes = $interval->i;
            $isToday = date("d/m/Y", $this->timestamp) == date("d/m/Y");
            if ($elapsedHours <= $maximumHoursDisplayed && $isToday)
            {
                if ($elapsedHours < 1)
                {
                    return $elapsedMinutes < 1 ? "à l'instant" : "Il y a " . $elapsedMinutes . " minute(s)";
                }
                else
                {
                    return "Il y a " . $elapsedHours . " heure(s)";
                }
            } elseif ($elapsedHours > $maximumHoursDisplayed && $isToday) {
                return "Aujourd'hui à " . date("H:i", $this->timestamp);
            }
            $dayText = date("d", $this->timestamp);
            $monthText = $this->getMonth();
            $hourText = date("H:i", $this->timestamp);
            return "$dayText $monthText, $hourText";
        }

        /**
         * @throws ValidationException
         * @throws Exception
         */
        public static function parse(string $textOfDate): FrenchDate
        {
            if (!($date = DateTime::createFromFormat('d/m/Y', $textOfDate))) {
                throw new ValidationException('incorrect birthdate given');
            }
            return new self($date->getTimestamp());
        }
    }
}