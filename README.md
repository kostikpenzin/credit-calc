# Credit Calculator

[![Latest Stable Version](https://poser.pugx.org/kostikpenzin/credit-calc/v/stable)](https://packagist.org/packages/kostikpenzin/credit-calc)
[![Total Downloads](https://poser.pugx.org/kostikpenzin/credit-calc/downloads)](https://packagist.org/packages/kostikpenzin/credit-calc)
[![Latest Unstable Version](https://poser.pugx.org/kostikpenzin/credit-calc/v/unstable)](https://packagist.org/packages/kostikpenzin/credit-calc)
[![License](https://poser.pugx.org/kostikpenzin/credit-calc/license)](https://packagist.org/packages/kostikpenzin/credit-calc)
[![Monthly Downloads](https://poser.pugx.org/kostikpenzin/credit-calc/d/monthly)](https://packagist.org/packages/kostikpenzin/credit-calc)

Calculation of the loan repayment schedule with the possibility of specifying early repayment payments. Both the annuity repayment order and the differentiated one are supported.

All monetary values in the package are integers and represent the sum with cents.

## Installation

Сomposer: `composer require kostikpenzin/credit-calc`

## Using

```php
require __DIR__ . '/../vendor/autoload.php';

use CreditCalc\Calculator;
use CreditCalc\CreditParams;
use CreditCalc\UnexpectedPayment;
use CreditCalc\RepaymentSchedule;

$params = new CreditParams(new DateTime('2023-10-31 00:00:00'), 100000000, 990, 12, CreditParams::DURATION_MONTH);
$unexpectedPayments = [
    new UnexpectedPayment(10000000, new DateTime('2023-12-15 00:00:00'), UnexpectedPayment::LESS_PAYMENT),
    new UnexpectedPayment(3565411, new DateTime('2023-01-13 00:00:00'), UnexpectedPayment::LESS_LOAN_PERIOD),
    new UnexpectedPayment(15000000, new DateTime('2023-02-29 00:00:00'), UnexpectedPayment::LESS_LOAN_PERIOD),
];

$calculator = new Calculator;

// we consider the repayment schedule with annuity payments
// So that there are differentiated payments, we specify Calculator::TYPE_TYPE_DIFFERENT as the third parameter

/** @var RepaymentSchedule $schedule Payment schedule */
$schedule = $calculator->calculate($params, $unexpectedPayments, Calculator::TYPE_ANNUITY);
foreach ($schedule as $repayment) {
    /** @var DateTime $date Date of the next payment period */
    $date = $repayment->getDate()->format('d.m.Y');

    /** @var int $payment The amount of the next payment */
    $payment = $repayment->getPayment();

    /** @var int $percents Interest on the next payment */
    $percents = $repayment->getPercents();

    /** @var int $body The body of the loan for the next payment */
    $body = $repayment->getBody();

    /** @var int $body Loan balance after payment */
    $balance = $repayment->getBalance();
}

/** @var int $total Get the amount of all loan payments */
$total = $schedule->calculateTotalPayments();

/** @var int $overpayment Get the amount of overpayment on the loan */
$overpayment = $schedule->calculateOverpayment();

/** @var int $requestedSum Get the loan amount */
$requestedSum = $schedule->getCreditParams()->getRequestedSum();

/** @var string psk The full cost of the loan as a percentage per annum with rounding up to 3 digits */
$psk = $schedule->calculateTotalCost();
```
