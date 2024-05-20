<?php

namespace socket\Message;

use uzdevid\property\loader\Entity;

class PrintMessage extends Entity {
    /**
     * @param string $message
     */
    public function __construct(
        public readonly string $message
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