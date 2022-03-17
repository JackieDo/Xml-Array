<?php

namespace Jackiedo\XmlArray\Tests\Traits;

trait AdaptivePhpUnit
{
    public function expectExceptionAndMessage($exception, $message = '')
    {
        // PHPUnit >= 5.2
        if (method_exists(parent::class, 'expectException') && method_exists(parent::class, 'expectExceptionMessage')) {
            parent::expectException($exception);
            parent::expectExceptionMessage($exception);

            return;
        }

        // PHPUnit < 5.2
        $this->setExpectedException($exception, $message);
    }
}
