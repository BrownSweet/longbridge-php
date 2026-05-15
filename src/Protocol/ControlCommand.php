<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2026-05-15 11:51
 */

namespace Brown\Longbridge\Protocol;

final class ControlCommand
{
    public const CLOSE = 0;
    public const HEARTBEAT = 1;
    public const AUTH = 2;
    public const RECONNECT = 3;
}