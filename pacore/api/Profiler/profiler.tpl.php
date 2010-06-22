<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
//  echo "<pre>" . print_r($timers,1) . "</pre>";
$pre_timer = array_shift($timers);
// pre_initialisation
$main_timer = array_shift($timers);
// main_timer
$unpf_timer = array_shift($timers);
// unprofiled_timer
$total_time = $pre_timer->getTime()+$main_timer->getTime();
$scale = 1;
?>

<style type="text/css">
    .prof_center {
       margin-left: auto;
       margin-right: auto;
       margin-bottom: 24px;
    }
    .prf_a {
        background-color: #fe9731;
        color: white;
        font-weight: bold;
    }
    .prf_h {
        background-color: black;
        color: white;
        font-weight: bold;
    }
    .prf_b {
        margin-left: auto;
        background-color: red;
        height: 5px;
    }
    .prf_t {
        background-color: #dddddd;
    }
</style>
<center>
<div class="prof_center">
<table cellpadding="4" cellspacing="1" style="background-color: #eeeeee; border: 1px solid black;">
  <tbody>
    <tr>
        <td colspan="5" class="prf_a"><b>Main statistic</td>
    </tr>
    <tr>
        <td class="prf_h">% percents</td>
        <td class="prf_h">duration</td>
        <td class="prf_h">block profiled</td>
        <td class="prf_h">description</td>
        <td class="prf_h"># calls</td>
    </tr>
    <tr>
      <td class="prf_t"><div class="prf_b" style="width: <?php echo 100*$scale?>px;"></div></td>
      <td><?php echo sprintf("%8.4f ms (%6.2f %%)", $total_time*1000, 100)?></td>
      <td><?php echo 'Total'?></td>
      <td><?php echo 'Total elapsed time'?></td>
      <td></td>
    </tr>
    <tr>
      <td class="prf_t"><div class="prf_b" style="width: <?php echo intval(($pre_timer->getTime()/$total_time)*100)?>px;"></div></td>
      <td><?php echo sprintf("%8.4f ms (%6.2f %%)", $pre_timer->getTime()*1000, ($pre_timer->getTime()/$total_time)*100)?></td>
      <td><?php echo $pre_timer->name?></td>
      <td><?php echo $pre_timer->description?></td>
      <td></td>
    </tr>
    <tr>
      <td class="prf_t"><div class="prf_b" style="width: <?php echo intval(($main_timer->getTime()/$total_time)*100)?>px;"></div></td>
      <td><?php echo sprintf("%8.4f ms (%6.2f %%)", $main_timer->getTime()*1000, ($main_timer->getTime()/$total_time)*100)?></td>
      <td><?php echo $main_timer->name?></td>
      <td><?php echo $main_timer->description?></td>
      <td></td>
    </tr>
    <tr>
      <td class="prf_t"><div class="prf_b" style="width: <?php echo intval(($unpf_timer->getTime()/$total_time)*100)?>px;"></div></td>
      <td><?php echo sprintf("%8.4f ms (%6.2f %%)", $unpf_timer->getTime()*1000, ($unpf_timer->getTime()/$total_time)*100)?></td>
      <td><?php echo $unpf_timer->name?></td>
      <td><?php echo $unpf_timer->description?></td>
      <td><?php echo $unpf_timer->call_counter?></td>
    </tr>
    <tr>
        <td colspan="5" class="prf_a"><b>Blocks statistic - Profiled <?php echo count($timers)?> blocks</td>
    </tr>
    <tr>
        <td class="prf_h">% percents</td>
        <td class="prf_h">duration</td>
        <td class="prf_h">block profiled</td>
        <td class="prf_h">description</td>
        <td class="prf_h"># calls</td>
    </tr>
    <?php foreach($timers as $timer) { : $percents = ($timer->getTime()/$total_time)*100?>
    <tr>
      <td class="prf_t"><div class="prf_b" style="width: <?php echo intval($percents)?>px;"></div></td>
      <td><?php echo sprintf("%8.4f ms (%6.2f %%)", $timer->getTime()*1000, $percents)?></td>
      <td><?php echo $timer->name?></td>
      <td><?php echo $timer->description?></td>
      <td><?php echo $timer->call_counter?></td>
    </tr>
    <?php endforeach;
}?>
    </tbody>
    </table>
</div>
</center>