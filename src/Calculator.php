<?php

namespace CreditCalc;

use InvalidArgumentException;
use CreditCalc\Types\AnnuitySimple;
use CreditCalc\Types\DifferentialSimple;
use CreditCalc\Types\Calculatable;


/**
 * Calculator
 */
class Calculator
{
    const TYPE_ANNUITY = 0;
    const TYPE_DIFFERENTIAL = 1;

    /**
     * Настройки расчета графика погашения кредита
     *
     * @var array
     */
    private $config;

    /**
     * @param array $config Массив с конфигурацией кредитного калькулятора
     * @throws InvalidArgumentException
     */
    public function __construct(array $config = null)
    {
        if (is_null($config)) {
            $config = [
                self::TYPE_ANNUITY => new AnnuitySimple(),
                self::TYPE_DIFFERENTIAL => new DifferentialSimple(),
            ];
        }

        foreach ($config as $concreteCalculator) {
            if (!$concreteCalculator instanceof Calculatable) {
                throw new InvalidArgumentException("Elements of array should implement Calculatable interface");
            }
        }
        $this->config = $config;
    }

    /**
     * Расчет графика погашения кредита по заданным параметрам
     *
     * @param CreditParams $creditParams Параметры кредита
     * @param array $unexpectedPayments Массив досрочных погашений
     * @param int $repaymentType Тип графика погашения, который необходимо рассчитать
     * @return RepaymentSchedule
     * @throws InvalidArgumentException
     */
    public function calculate(CreditParams $creditParams, array $unexpectedPayments = [], int $repaymentType = self::TYPE_ANNUITY): RepaymentSchedule
    {
        foreach ($this->config as $configRepaymentType => $concreteCalculator) {
            if ($repaymentType !== $configRepaymentType) {
                continue;
            }

            return $concreteCalculator->getRepaymentSchedule($creditParams, $unexpectedPayments);
        }

        throw new InvalidArgumentException("Calculator for credit type '{$repaymentType}' not found");
    }
}
