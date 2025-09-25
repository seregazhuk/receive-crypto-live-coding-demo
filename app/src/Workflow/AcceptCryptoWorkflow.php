<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Activity\AddressActivity;
use App\Activity\SweepingActivity;
use App\AddressWithAmount;
use App\RefillAmount;
use Generator;
use Temporal\Activity\ActivityOptions;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
class AcceptCryptoWorkflow
{
    private const string WAIT_FOR_PAYMENT_TIMEOUT = '10 minutes';
    /**
     * @var true
     */
    private bool $paymentReceived = false;

    public function __construct() {
        $this->addressActivity = Workflow::newActivityStub(
            AddressActivity::class,
            ActivityOptions::new()->withStartToCloseTimeout(10)
        );

        $this->sweepingActivity = Workflow::newActivityStub(
            SweepingActivity::class,
            ActivityOptions::new()->withStartToCloseTimeout(10)
        );
    }

    #[WorkflowMethod(name: 'acceptCrypto')]
    public function acceptCrypto(AddressWithAmount $request): Generator
    {
        $waitingBalance = Workflow::async(
            function () use ($request) {
                while (! yield $this->addressActivity->hasEnoughBalance($request)) {
                    yield Workflow::timer(3);
                }
                $this->paymentReceived = true;
            }
        );
        yield Workflow::awaitWithTimeout(self::WAIT_FOR_PAYMENT_TIMEOUT, fn() => $this->paymentReceived);
        if (!$this->paymentReceived) {
            $waitingBalance->cancel();
        }

        /** @var RefillAmount $refillAmount */
        $refillAmount = yield $this->sweepingActivity->calcRefillAmount();
        $refillRequest = new AddressWithAmount($request->address, $refillAmount->amount);
        yield $this->sweepingActivity->refill($refillRequest);

        $balanceWithRefill = $request->amount->add($refillAmount->amount);
        $balanceCheckRequest = new AddressWithAmount($request->address, $balanceWithRefill);
        while (! yield $this->addressActivity->hasEnoughBalance($balanceCheckRequest)) {
            yield Workflow::timer(3);
        }

        yield $this->sweepingActivity->sweep($request);
    }
}


