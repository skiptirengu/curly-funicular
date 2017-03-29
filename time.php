<?php
@date_default_timezone_set('America/Sao_Paulo');

function change_date_intervals($args, $add = true)
{
    $reference = new DateTimeImmutable;
    $endTime = clone $reference;

    foreach ($args as $dateInterval) {
        if ($add) {
            $endTime = $endTime->add($dateInterval);
        } else {
            $endTime = $endTime->sub($dateInterval);
        }
    }

    return $reference->diff($endTime);
}

function print_result(DateTime $time)
{
    echo "\n" . chr(27) . "[42m Last period should end at {$time->format('d/m/Y H:i')}" . chr(27) . "[0m \n\n";
}

//---------------------------------------------
// SUCC
//---------------------------------------------
/** @var DateTime[] $arguments */
$total_interval = new DateInterval('PT8H');
$arguments = array_map(function ($param) {
    return DateTime::createFromFormat('H:i', $param, new DateTimeZone('America/Sao_Paulo'));
}, explode(' ', str_replace(',', '', $argv[1])));

if (count($arguments) == 2) {
    $last = end($arguments);
    $cpLast = clone $last;
    $arguments[] = $cpLast->add(new DateInterval('PT60M'));
} elseif (count($arguments) % 2 == 0) {
    echo "Parameters must be odd\n";
    return;
} elseif (count($arguments) == 1) {
    $first_time = array_shift($arguments);
    $first_time->add($total_interval);
    print_result($first_time);
    return;
}


//---------------------------------------------
// MORE THAN ONE PERIOD
//---------------------------------------------
$time_chunks = array_chunk($arguments, 2);
/** @var DateInterval $period_time */
$period_time = null;
$final_time = null;
/** @var DateTime[] $chunk */
foreach ($time_chunks as $idx => $chunk) {
    if (count($chunk) == 2) {
        if (empty($period_time)) {
            $period_time = $chunk[0]->diff($chunk[1]);
        } else {
            $period_time = change_date_intervals([$period_time, $chunk[0]->diff($chunk[1])]);
        }
    } else {
        if ($idx == count($time_chunks) - 1) {
            // last period
            $date_im1 = new DateTimeImmutable;
            $date_im2 = clone $date_im1;
            $final_time = $chunk[0]->add($date_im2->diff($date_im1->add($total_interval)->sub($period_time)));
            print_result($final_time);
            break;
        }
    }
}
