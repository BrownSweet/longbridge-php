<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 11:57
 */

namespace Brown\Longbridge\Socket;

final class RequestIdGenerator
{
    private int $current = 0;

    public function next(): int
    {
        $this->current++;

        if ($this->current > 0xffffffff) {
            $this->current = 1;
        }

        return $this->current;
    }
}