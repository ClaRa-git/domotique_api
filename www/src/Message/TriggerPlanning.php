<?php

namespace App\Message;

final class TriggerPlanning
{
    public function __construct(
        public readonly int $planningId,
    ) {
    }
}
