<?php

namespace CreditCalc;


/**
 * UnexpectedPayment
 */
class UnexpectedPayment
{
    const LESS_PAYMENT = 0;
    const LESS_LOAN_PERIOD = 1;

    /** @var int */
    private $amount;

    /** @var \DateTime */
    private $date;

    /** @var int */
    private $type;

    /**
     * Payment constructor.
     *
     * @param int $amount The payment amount in kopecks.
     * @param \DateTime $date The payment date.
     * @param int $type The type of payment, which affects how the loan is recalculated.
     *
     * @throws \InvalidArgumentException If an unexpected payment type is provided.
     */
    public function __construct(int $amount, \DateTime $date, int $type = self::LESS_LOAN_PERIOD)
    {
        // Check if the payment type is valid.
        if ($type !== self::LESS_LOAN_PERIOD && $type !== self::LESS_PAYMENT) {
            throw new \InvalidArgumentException('Unexpected payment invalid type');
        }

        // Set the amount, date, and type properties.
        $this->amount = $amount;
        $this->date = $date;
        $this->type = $type;
    }


    /**
     * This function returns the date of payment.
     *
     * @return \DateTime Returns a DateTime object representing the date of payment.
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }


    /**
     * This function returns the payment amount as an integer.
     *
     * @return int The payment amount.
     */
    public function getAmount(): int
    {
        // Return the payment amount.
        return $this->amount;
    }

    /**
     * Returns the type of credit influence as an integer.
     *
     * @return int The type of credit influence.
     */
    public function getType(): int
    {
        return $this->type;
    }
}
