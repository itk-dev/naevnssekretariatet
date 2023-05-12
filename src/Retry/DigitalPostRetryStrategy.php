<?php

namespace App\Retry;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Digital post retry strategy.
 */
final class DigitalPostRetryStrategy implements RetryStrategyInterface
{
    private readonly array $waitingTimes;

    public function __construct(array $options)
    {
        $options = (new OptionsResolver())
            ->setRequired('waiting_times')
            ->setAllowedTypes('waiting_times', 'int[]')
            ->setAllowedValues('waiting_times', static fn (array $value) => array_is_list($value))
            ->resolve($options)
        ;

        $this->waitingTimes = $options['waiting_times'];
    }

    public function isRetryable(Envelope $message): bool
    {
        $retries = RedeliveryStamp::getRetryCountFromEnvelope($message);

        return $retries < count($this->waitingTimes);
    }

    /**
     * {@inheritdoc}
     */
    public function getWaitingTime(Envelope $message): int
    {
        $retries = RedeliveryStamp::getRetryCountFromEnvelope($message);

        // Waiting time in seconds (with a fallback to 60).
        $waitingTime = $this->waitingTimes[$retries] ?? 60;

        // Return in milliseconds.
        return 1000 * $waitingTime;
    }
}
