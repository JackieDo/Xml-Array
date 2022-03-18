<?php

namespace Jackiedo\XmlArrayTests\Traits;

/**
 * A trait for adapting to PHPUnit versions before and after 5.2.
 */
trait AdaptivePHPUnit
{
    public function setExpectedException($exception, $message = '', $code = null)
    {
        // PHPUnit >= 5.2
        if (version_compare($this->getPHPUnitVersion(), '5.2.0', '>=')) {
            $this->expectException($exception);

            if (!empty($message)) {
                $this->expectExceptionMessage($message);
            }

            if (!empty($code)) {
                $this->expectExceptionCode($code);
            }

            return;
        }

        // PHPUnit < 5.2
        parent::setExpectedException($exception, $message, $code);
    }

    protected function getPHPUnitVersion()
    {
        if (class_exists('\PHPUnit\Runner\Version')) {
            return \PHPUnit\Runner\Version::id();
        }

        return \PHPUnit_Runner_Version::id();
    }
}
