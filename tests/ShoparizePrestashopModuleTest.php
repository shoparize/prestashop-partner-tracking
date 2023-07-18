<?php

if (class_exists('LegacyTests\Unit\ContextMocker')) {
    class_alias('LegacyTests\Unit\ContextMocker', 'Tests\Unit\ContextMocker');
}

class ShoparizePrestashopModuleTest extends \PHPUnit\Framework\TestCase
{
    public function testSuccessInstall()
    {
        $module = new Shoparizepartner();
        $result = $module->install();
        $this->assertTrue($result);
    }
}
