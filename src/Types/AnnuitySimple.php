<?php

namespace CreditCalc\Types;

use CreditCalc\CreditParams;
use CreditCalc\RepaymentParams;
use CreditCalc\RepaymentSchedule;
use CreditCalc\UnexpectedPayment;


/**
 * AnnuitySimple
 */
class AnnuitySimple implements Calculatable
{
    public function getRepaymentSchedule(CreditParams $params, array $unexpectedPayments): RepaymentSchedule
    {
        $initialDate = $params->getInitialDate();
        $requestedSum = $params->getRequestedSum();
        $percents = $params->getPercents();
        $repaymentsCount = $params->getRepaymentPeriodsCount();
        $durationType = $params->getDurationType();

        $schedule = [
            new RepaymentParams($initialDate, 0, 0, $requestedSum)
        ];

        $fullPeriodsCount = floor(365 / CreditParams::$daysInPaymentPeriod[$durationType]);
        $repaymentRounded = $this->calcPaymentAmount($requestedSum, $percents, $repaymentsCount, $fullPeriodsCount);

        $previousRepaymentDate = clone $initialDate;
        $remainingAmount = $requestedSum;
        for ($i = 1; $i <= $repaymentsCount; $i++) {
            $currentRepaymentDate = \CreditCalc\addDurationToDate($initialDate, $durationType, $i);
            $interval = $currentRepaymentDate->diff($previousRepaymentDate);

            // Комиссия
            $comission = $params->getPeriodicComission();
            if ($i === 1) {
                $comission += $params->getOneTimeComission();
            }

            // Выплачено процентов
            $percentsRepayed = round($remainingAmount * $percents / ($fullPeriodsCount * 10000));

            // Платежи досрочного погашения, которые есть в периоде
            $prevUnexpectedRepaymentDate = clone $previousRepaymentDate;
            $unexpectedPaymentsInPeriod = \CreditCalc\getUnexpectedPaymentsBetweenDates($previousRepaymentDate, $currentRepaymentDate, $unexpectedPayments);
            foreach ($unexpectedPaymentsInPeriod as $unexpectedPayment) {
                $recalcType = $unexpectedPayment->getType();
                $unexpectedPaymentDate = $unexpectedPayment->getDate();
                $unexpectedAmount = $unexpectedPayment->getAmount();
                $interval = $unexpectedPaymentDate->diff($prevUnexpectedRepaymentDate);

                $remainingAmount -= $unexpectedAmount;
                if ($recalcType === UnexpectedPayment::LESS_PAYMENT) {
                    $repaymentRounded = $this->calcPaymentAmount($remainingAmount, $percents, $repaymentsCount - $i + 1, $fullPeriodsCount);
                    $percentsRepayed  = round($remainingAmount * $percents / ($fullPeriodsCount * 10000));
                }
                if ($recalcType === UnexpectedPayment::LESS_LOAN_PERIOD) {
                    $percentsInPeriod = $percents / ($fullPeriodsCount * 10000);
                    $log = $percentsInPeriod / ($repaymentRounded / $remainingAmount - $percentsInPeriod) + 1;
                    $newRepaymentsCount = (int)round(log($log, 1 + $percentsInPeriod) + 0.5);
                    $repaymentsCount = $i + $newRepaymentsCount - 1;
                }

                $schedule[] = new RepaymentParams($unexpectedPaymentDate, 0, $unexpectedAmount, $remainingAmount);

                $prevUnexpectedRepaymentDate = $unexpectedPaymentDate;
            }

            // Выплачено всего
            $currentRepayment = $repaymentRounded;
            if ($i === $repaymentsCount) {
                $currentRepayment = $remainingAmount + $percentsRepayed;
            }

            // Выплачено тело долга
            $bodyRepayed = $currentRepayment - $percentsRepayed;

            $previousRepaymentDate = $currentRepaymentDate;
            $remainingAmount -= $bodyRepayed;

            $schedule[] = new RepaymentParams($currentRepaymentDate, $percentsRepayed, $bodyRepayed, $remainingAmount, $comission);
        }

        return new RepaymentSchedule($schedule, $params);
    }

    private function calcPaymentAmount(int $creditAmount, int $percents, int $repaymentsCount, int $fullPeriodsCount)
    {
        $percentsInPeriod = $percents / ($fullPeriodsCount * 10000);
        $neededExp = pow(1 + $percentsInPeriod, $repaymentsCount);
        $annuitetKoefficient = ($percentsInPeriod * $neededExp) / ($neededExp - 1);
        $repayment = $annuitetKoefficient * $creditAmount;
        $repaymentRounded = round($repayment);

        return $repaymentRounded;
    }
}
