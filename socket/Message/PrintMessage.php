<?php

namespace socket\Message;

use uzdevid\property\loader\Entity;

class PrintMessage extends Entity {
    /**
     * @param string $message
     * @param bool $exit
     */
    public function __construct(
        public readonly string $message,
        public readonly bool   $exit = true
    ) {
        parent::__construct();
    }

    /**
     * @return string
     */
    public static function methodName(): string {
        return 'PrintMessage';
    }
}