<?php

namespace Tests\Unit;

use Tests\TestCase;

class DetectExpressionTest extends TestCase
{
    public function test_grief_priority_over_emergency()
    {
        $ref = new \ReflectionClass(\App\Services\GroqAIService::class);
        $svc = $ref->newInstanceWithoutConstructor();
        $method = $ref->getMethod('detectExpression');
        $method->setAccessible(true);

        $user = 'Acaba de morir mi gatito';
        $ai = '';
        $history = [['role'=>'user','content'=>'hola'], ['role'=>'assistant','content'=>'Hola']];

        $expr = $method->invoke($svc, $user, $ai, $history);
        $this->assertContains($expr, ['3-3','1-3'], 'Expected a grief-related expression');
    }
}
