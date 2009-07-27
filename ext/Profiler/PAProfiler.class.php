<?php
require_once "api/Theme/Template.php";
require_once "ext/Profiler/PATimer.class.php";

/**
 * @Class PAProfiler
 *
 * This class implements basic method for code profiling
 *
 * @author     Zoran Hron <zhron@broadbandmechanics.com>
 * @version    0.1.0
 *
 * @example
 *            <code>
 *               $profiler->startTimer('MyTimer', 'This is my timer');
 *               ...
 *               ...
 *               $profiler->stopTimer('MyTimer');
 *            </code>
 *
 *
 */
class PAProfiler {
    private $timers;
    private $root_timer;
    private $unprof_timer;
    private $depth;
    public  $html;

    public function __construct() {
      $this->timers = array();
      $this->init();
    }

    /**
    * Initialise timers
    **/
    public function init() {
      $preInit = new PATimer('pre_initialisation', 'Pre Initialisation');
      $preInit->start_time = $_SERVER['REQUEST_TIME'];
      $preInit->suspend();
      $this->timers['pre_initialisation'] = $preInit;

      $root_timer = new PATimer('main_timer', 'Total execution time');
      $this->timers['main_timer'] = $root_timer;
      $this->timers['main_timer']->start();
      $this->root_timer = &$this->timers['main_timer'];

      $unprof_timer = new PATimer('unprofiled_timer', 'Unprofiled code');
      $this->timers['unprofiled_timer'] = $unprof_timer;
      $this->timers['unprofiled_timer']->start();
      $this->unprof_timer = &$this->timers['unprofiled_timer'];
      $this->depth = 3;
    }

    /**
    *   Start an individual timer
    *
    *   @param string $name name of the timer
    *   @param string optional $desc description of the timer
    **/
    public function startTimer($name, $desc = null) {
      if($this->depth <= 3) {
        $this->unprof_timer->suspend();
      }
      if(!isset($this->timers[$name])) {
        ++$this->depth;
        $timer = new PATimer($name, $desc);
        $this->timers[$name] = $timer;
        $this->timers[$name]->start();
      } else {
        $this->timers[$name]->restart();
      }
    }

    /**
    *   Stop an individual timer
    *   Restart the root timer
    *   @param string $name name of the timer
    **/
    public function stopTimer($name){
      if(isset($this->timers[$name])) {
        $this->timers[$name]->stop();
        --$this->depth;
        if($this->depth <= 3) {
          $this->unprof_timer->resume();
        }
      }
    }

    public function done() {
      $this->unprof_timer->suspend();
      $this->root_timer->stop();
      $this->root_timer->elapsed_time = $this->root_timer->getTime();
      $this->html = $this->getProfilerHtml();
    }

    public function showResults() {
      echo $this->html;
    }

    public function getProfilerHtml() {
       $html = & new Template(dirname(__FILE__) . '/profiler.tpl.php');
       $html->set('timers', $this->timers);
       return $html->fetch();
    }
}
?>