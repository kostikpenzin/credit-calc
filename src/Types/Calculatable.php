<?php

namespace CreditCalc\Types;

use CreditCalc\CreditParams;
use CreditCalc\RepaymentSchedule;


interface Calculatable
{
    /**
     * Вернуть график платежей,
     * где в качестве ключа массива лежит дата погашения,
     * а в качестве значения сумма пошашения
     *
     * @params CreditParams $params Параметры кредита
     * @params array $unexpectedPayments Досрочные погашения
     * @return RepaymentSchedule
     */
    public function getRepaymentSchedule(CreditParams $params, array $unexpectedPayments): RepaymentSchedule;
}
