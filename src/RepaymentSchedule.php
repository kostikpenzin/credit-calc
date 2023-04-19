<?php

namespace CreditCalc;


/**
 * RepaymentSchedule
 */
class RepaymentSchedule implements \Iterator, \Countable
{
    /** @var int */
    private $position;

    /**
     * An array containing the repayment graph data.
     */
    private $repayments;

    /** @var CreditParams */
    private $creditParams;

    /** @var int */
    private $creditTotalCost;

    /**
     * Constructs a new instance of the class.
     *
     * @param array $repayments An array of repayment parameters.
     * @param CreditParams $params The credit parameters.
     * @throws InvalidArgumentException If an array element is not an instance of RepaymentParams class.
     */
    public function __construct(array $repayments, CreditParams $params)
    {
        // Check that each element in the $repayments array is an instance of the RepaymentParams class.
        foreach ($repayments as $repayment) {
            if (!($repayment instanceof RepaymentParams)) {
                throw new \InvalidArgumentException('Element of array does not instance of \CreditCalc\RepaymentParams class');
            }
        }

        // Set the initial position of the repayment schedule to 0.
        $this->position = 0;

        // Set the repayment schedule and credit parameters.
        $this->repayments = $repayments;
        $this->creditParams = $params;
    }


    /**
     * Returns the initial credit parameters.
     *
     * @return CreditParams The initial credit parameters.
     */
    public function getCreditParams(): CreditParams
    {
        // Return the credit parameters stored in the object.
        return $this->creditParams;
    }


    /**
     * Returns the current repayment parameters from the repayment graph.
     *
     * @return RepaymentParams The current repayment parameters.
     *
     * @throws \RangeException If the current position is invalid.
     */
    public function current(): RepaymentParams
    {
        // Check if current position is valid.
        if (!$this->valid()) {
            $count = count($this->repayments);
            throw new \RangeException("Invalid position. Position: {$this->position}, elements count: {$count}");
        }

        // Return the repayment parameters at the current position.
        return $this->repayments[$this->position];
    }

    /**
     * Returns the current repayment number.
     *
     * @return int The current repayment number.
     */
    public function key(): int
    {
        // The position variable represents the current repayment number.
        // It is initialized to 0 by default and updated as the repayments
        // are processed.
        return $this->position;
    }

    /**
     * Advances to the next payment element.
     *
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * This method sets the position of the current element to the first element in the repayment schedule.
     *
     * @return void
     */
    public function rewind(): void
    {
        // Set the position to the first element
        $this->position = 0;
    }


    /**
     * Checks if the current position is valid.
     * 
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->repayments[$this->position]);
    }

    /**
     * Returns the number of elements in the repayment graph.
     * 
     * @return int The number of elements in the repayment graph.
     */
    public function count(): int
    {
        return count($this->repayments);
    }

    /**
     * Return the full cost of the loan
     *
     * @return string
     */
    public function calculateTotalCost(): string
    {
        // Check if the total cost has already been calculated
        if (!is_null($this->creditTotalCost)) {
            return $this->creditTotalCost;
        }

        // Initialize arrays for dates, payments, e, and q
        $dates = [];
        $payments = [];
        $e = [];
        $q = [];

        // Get the credit duration type and the number of days in each payment period
        $creditDuration = $this->creditParams->getDurationType();
        $daysInBasePeriod = CreditParams::$daysInPaymentPeriod[$creditDuration];

        /** @var \DateTime $initialDate */
        // Get the initial date of the repayments
        $initialDate = $this->repayments[0]->getDate();

        // Loop through each repayment and populate the dates, payments, e, and q arrays
        for ($i = 0; $i < count($this->repayments); $i++) {
            /** @var RepaymentParams $repayment */
            $repayment = $this->repayments[$i];

            $dates[] = $repayment->getDate();
            if ($i === 0) {
                // If it's the first repayment, add the negative balance to payments
                $payments[] = -$repayment->getBalance();
            } else {
                // Otherwise, add the payment amount to payments
                $payments[] = $repayment->getPayment();
            }

            // Calculate the interval between the repayment date and the initial date
            $interval = $repayment->getDate()->diff($initialDate);
            $daysDiff = $interval->days;

            // Calculate the value of e and q
            $e[] = ($daysDiff % $daysInBasePeriod) / $daysInBasePeriod;
            $q[] = floor($daysDiff / $daysInBasePeriod);
        }

        $basePeriodsCount = 365 / $daysInBasePeriod;
        $i = 0;
        $x = 1;
        $x_m = 0;
        $s = 0.000001;

        // Loop until x is less than or equal to zero
        while ($x > 0) {
            $x_m = $x;
            $x = 0;

            // Loop through each payment and calculate x
            for ($k = 0; $k < count($dates); $k++) {
                $x = $x + $payments[$k] / ((1 + $e[$k] * $i) * pow(1 + $i, $q[$k]));
            }

            // Increment i
            $i = $i + $s;
        }

        // If x is greater than x_m, decrement i by s
        if ($x > $x_m) {
            $i = $i - $s;
        }

        // Calculate the total cost of the credit and format it as a string
        $this->creditTotalCost = number_format($i * $basePeriodsCount * 100, 3, '.', '');

        // Return the total cost of the credit
        return $this->creditTotalCost;
    }

    /**
     * Returns the total payments made towards the credit.
     *
     * @return int The total amount paid towards the credit.
     */
    public function calculateTotalPayments(): int
    {
        // Use array_reduce to sum the payments in the $repayments array.
        return array_reduce($this->repayments, function (int $carry, $repayment) {
            $carry += $repayment->getPayment();
            return $carry;
        }, 0);
    }

    /**
     * Calculates the overpayment amount for a credit.
     *
     * @return int The overpayment amount.
     */
    public function calculateOverpayment(): int
    {
        // Reduce the array of repayments to a single sum of percents.
        return array_reduce($this->repayments, function (int $carry, $repayment) {
            // Add the percents of this repayment to the accumulated sum.
            $carry += $repayment->getPercents();
            // Return the updated sum for the next iteration.
            return $carry;
        }, 0);
    }
}
