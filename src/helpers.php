<?php

namespace CreditCalc;

use DateTime;
use DateInterval;

/**
 *
 * Calculates the date of the next payment
 *
 * @param DateTime $initial Начальная дата (initial date)
 * @param int $durationType Продолжительность платежного периода (duration type)
 * @param int $repaymentNumber Номер платежного периода (repayment number)
 * @return DateTime
 * @throws \InvalidArgumentException
 */
function addDurationToDate(DateTime $initial, int $durationType, int $repaymentNumber): DateTime
{
    // Check if repaymentNumber is valid
    if ($repaymentNumber <= 0) {
        throw new \InvalidArgumentException('Invalid argument repaymentNumber');
    }

    // Clone the initial date to avoid modifying it
    $newDate = clone $initial;

    // Calculate the date based on the duration type
    if (
        $durationType === CreditParams::DURATION_WEEK
        || $durationType === CreditParams::DURATION_TWO_WEEKS
        || $durationType === CreditParams::DURATION_QUARTER
    ) {
        // Calculate the number of days in the payment period
        $intervalDays = CreditParams::$daysInPaymentPeriod[$durationType];
        $days = $intervalDays * $repaymentNumber;
        $interval = new DateInterval("P{$days}D");
        return $newDate->add($interval);
    } elseif ($durationType === CreditParams::DURATION_MONTH) {
        // Calculate the date for monthly payments
        $newDate->add(new DateInterval("P{$repaymentNumber}M"));

        // Check if the date is in the same month or the next month
        $initialMonthsCount = (int)$initial->format('Y') * 12 + (int)$initial->format('n');
        $newMonthsCount = (int)$newDate->format('Y') * 12 + (int)$newDate->format('n');
        if ($newMonthsCount - $initialMonthsCount > $repaymentNumber) {
            $newDate->modify('last day of previous month');
        }
        return $newDate;
    }

    // Throw an exception if durationType is invalid
    throw new \InvalidArgumentException('Invalid argument durationType');
}

/**
 *
 * @param DateTime $currentDate Дата, начиная с которой нужно получить платежи
 * @param DateTime $nextDate Дата, до которой нужно получить платежи
 * @param array<UnexpectedPayment> $unexpectedPayments Массив платежей
 * @return array<UnexpectedPayment> Массив платежей, удовлетворяющих условию
 * @throws \InvalidArgumentException Если в массиве не все элементы являются экземплярами UnexpectedPayment
 */
function getUnexpectedPaymentsBetweenDates(DateTime $currentDate, DateTime $nextDate, array $unexpectedPayments): array
{
    $payments = [];

    $currDateStr = $currentDate->format('Y-m-d');
    $nextDateStr = $nextDate->format('Y-m-d');
    foreach ($unexpectedPayments as $unexpectedPayment) {
        // Проверяем, что текущий элемент массива является экземпляром UnexpectedPayment
        if (!($unexpectedPayment instanceof UnexpectedPayment)) {
            throw new \InvalidArgumentException('Array should contain instances of UnexpectedPayment');
        }
        $paymentDateStr = $unexpectedPayment->getDate()->format('Y-m-d');
        if (
            $paymentDateStr >= $currDateStr
            && $paymentDateStr < $nextDateStr
        ) {
            $payments[] = $unexpectedPayment;
        }
    }

    // Сортируем массив платежей по дате
    uasort($payments, function ($a, $b) {
        $aTs = (int)$a->getDate()->format('U');
        $bTs = (int)$b->getDate()->format('U');
        if ($aTs === $bTs) {
            return 0;
        }
        return ($aTs < $bTs) ? -1 : 1;
    });

    return $payments;
}

/**
 * Форматирует денежную сумму, выраженную в копейках в красивый вид.
 *
 * Например, 1500000 копеек -> 15 000.00 рублей.
 *
 * @param int $number Число, которое нуждается форматировании.
 * @return string Отформатированная строка.
 */
function prettifyNumber(int $number): string
{
    // Делим число на 100, чтобы перевести его из копеек в рубли.
    $rubles = $number / 100;

    // Форматируем число с двумя знаками после запятой и разделителем тысяч.
    $formatted = number_format($rubles, 2, '.', ' ');

    return $formatted . ' рублей';
}
