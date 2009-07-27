<?php

/**
 * @Class PATimer
 *
 * This class implements basic timer object and methods
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 *
 */
class PATimer {
    public $name;
    public $description;
    public $start_time;
    public $stop_time;
    public $interval;
    public $elapsed_time;
    public $call_counter;

    public function __construct($name, $description = null) {
      $this->name = $name;
      $this->description = $description;
      $this->interval = 0;
      $this->start_time = 0;
      $this->stop_time = 0;
      $this->elapsed_time = 0;
      $this->call_counter = 0;
    }

    public function start() {
      $this->start_time = microtime(true);
      $this->interval = 0;
      ++$this->call_counter;
    }

    public function stop() {
      $this->stop_time = microtime(true);
      $this->interval = $this->stop_time - $this->start_time;
    }

    public function suspend() {
      $this->stop();
      $this->elapsed_time += $this->interval;
    }

    public function resume() {
      $this->start();
      $this->stop_time = $this->start_time;
    }

    public function restart() {
      $this->elapsed_time += $this->interval;
      $this->resume();
    }

    public function getTime() {
      return (($this->elapsed_time == 0) ? $this->interval : $this->elapsed_time);
    }
}
?>