<?php

/**
 * 格式化输入时间
 *
 * @param $time
 * @return false|int
 */
function timeFormat($time) {
    //如果是空，返回当前时间戳
    if (empty($time)) return time();

    // 如果是整数，认为是时间戳，直接返回
    if (is_int($time)) return $time;

    // 格式化时间
    $timestamp = strtotime($time);
    if ($timestamp) {
        // 如果是时间，返回时间戳
        return $timestamp;
    } else{
        // 不是时间，返回当前时间戳
        return time();
    }
}