<?php

namespace CreditCalc;

use DateTime;


/**
 * RepaymentParams
 */
class RepaymentParams
{
    /**
     * @var DateTime 
     */
    private $date;

    /**
     * @var int 
     */
    private $payment;

    /**
     * @var int
     */
    private $percents;

    /**
     * @var int
     */
    private $body;

    /**
     * @var int 
     */
    private $balance;

    /**
     * @var int 
     */
    private $comission;

    /**
     * Class constructor.
     * 
     * @param DateTime $date The date of the payment.
     * @param int $percents The percentage of the payment.
     * @param int $body The body of the payment.
     * @param int $balance The balance of the payment.
     * @param int $comission The commission of the payment (optional, defaults to 0).
     */
    public function __construct(DateTime $date, int $percents, int $body, int $balance, int $comission = 0)
    {
        // Set the payment date.
        $this->date = $date;
        // Set the payment percentage.
        $this->percents = $percents;
        // Set the payment body.
        $this->body = $body;
        // Set the payment balance.
        $this->balance = $balance;
        // Set the payment commission.
        $this->comission = $comission;
        // Calculate the total payment amount.
        $this->payment = $percents + $body + $comission;
    }

    /**
     * Returns the date associated with this object.
     * 
     * @return DateTime The date associated with this object.
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * Returns the total payment amount.
     *
     * @return int The total payment amount.
     */
    public function getPayment(): int
    {
        return $this->payment;
    }

    /**
     * Returns the percentage of the credit in the payment.
     *
     * @return int The percentage of the credit in the payment.
     */
    public function getPercents(): int
    {
        return $this->percents;
    }

    /**
     * Returns the body of the credit payment
     *
     * @return int The body of the credit payment
     */
    public function getBody(): int
    {
        return $this->body;
    }

    /**
     * Returns the remaining balance on the credit account after a payment is made.
     *
     * @return int The remaining balance on the credit account.
     */
    public function getBalance(): int
    {
        return $this->balance;
    }

    /**
     * Get the commission for the payment.
     *
     * @return int The commission amount.
     */
    public function getCommission(): int
    {
        return $this->comission;
    }
}
