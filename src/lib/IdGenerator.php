<?php
namespace ShortFlake;

use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

/**
 * Generate 64bits UUID based on the following formula
 *    42bits: number of millisecond epocs (1000 milliseconds = 1 second) since 1420099200; // 2015-01-01 00:00:00
 *    10bits: generator-id (total 1024 generator instances 0-1023)
 *    12bits: an increment number (smaller than 4096)
 */
class IdGenerator {

    /**
     * 2015-01-01 00:00:00
     */
    const ORIGIN_EPOC = 1420099200;

    /**
     * Number of epocs in millisecond from 2015-01-01 00:00:00
     *
     * @var int
     */
    private $current_epoc; //

    /**
     * A unique-id of a generator.
     *
     * @var int
     */
    private $generator_id;

    /**
     * @var int
     */
    private $incremental = 0;

    /**
     * @var int
     */
    private $total_generated_ids = 0;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * ShortUUID constructor.
     *
     * @param LoopInterface $loop
     * @param int           $generator_id
     */
    public function __construct(LoopInterface $loop, $generator_id = 1) {
        $this->loop         = $loop;
        $this->generator_id = (int)$generator_id;

        $this->resetCounter();

        // reset the counter every 1 milliseconds
        $this->loop->addPeriodicTimer(0.001, array($this, 'resetCounter'));
    }

    /**
     * @return \React\Promise\Promise|PromiseInterface
     */
    public function computeId() {
        $deferred = new Deferred();

        $this->loop->nextTick($this->doCompute($deferred));

        return $deferred->promise();
    }

    /**
     * Reset the epoc and the counter every 1 milliseconds
     * Please notices: we only have 4096 ids per milliseconds epoc.
     */
    public function resetCounter() {
        $this->total_generated_ids = $this->total_generated_ids + $this->incremental;
        $this->current_epoc        = (int) ((microtime(1) - self::ORIGIN_EPOC) * 1000); // epocs in milliseconds
        $this->incremental         = 0;
    }

    /**
     * Return the number of generated ids since started.
     *
     * @return int
     */
    public function getTotalGeneratedIds() {
        return $this->total_generated_ids;
    }

    /**
     * @param Deferred $deferred
     *
     * @return \Closure
     */
    private function doCompute(Deferred $deferred) {
        $fnc = function () use ($deferred) {
            // if we still have enough id in this epoc, compute it
            if ($this->incremental < 4096) {
                $deferred->resolve(($this->current_epoc << 22) | ($this->generator_id << 12) | ($this->incremental++));
            }
            // if no id left, schedule the computation in the next tick
            else {
                $this->loop->futureTick($this->doCompute($deferred));
            }
        };

        $fnc->bindTo($this);

        return $fnc;
    }
}