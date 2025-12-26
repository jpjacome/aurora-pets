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

    public function test_misspelling_murioi_detected_as_grief()
    {
        $ref = new \ReflectionClass(\App\Services\GroqAIService::class);
        $svc = $ref->newInstanceWithoutConstructor();
        $method = $ref->getMethod('detectExpression');
        $method->setAccessible(true);

        $user = 'se murioi mi planta que tenia las cenizas de mi mascota y estoy muy triste. no se que hacer';
        $ai = '';
        $history = [['role'=>'user','content'=>'hola'], ['role'=>'assistant','content'=>'Hola']];

        $expr = $method->invoke($svc, $user, $ai, $history);
        $this->assertContains($expr, ['3-3','1-3'], 'Expected compassionate grief expression for misspelling + ashes + sadness');
    }

    public function test_cenizas_plus_triste_combination()
    {
        $ref = new \ReflectionClass(\App\Services\GroqAIService::class);
        $svc = $ref->newInstanceWithoutConstructor();
        $method = $ref->getMethod('detectExpression');
        $method->setAccessible(true);

        $user = 'tengo las cenizas de mi mascota y estoy muy triste, no se que hacer';
        $ai = '';
        $history = [['role'=>'user','content'=>'hola'], ['role'=>'assistant','content'=>'Hola']];

        $expr = $method->invoke($svc, $user, $ai, $history);
        $this->assertContains($expr, ['3-3','1-3'], 'Expected compassionate grief expression for ashes + sadness combination');
    }

    public function test_non_grief_message_not_flagged()
    {
        $ref = new \ReflectionClass(\App\Services\GroqAIService::class);
        $svc = $ref->newInstanceWithoutConstructor();
        $method = $ref->getMethod('detectExpression');
        $method->setAccessible(true);

        $user = '¿Buscas una planta para interior o exterior? ¿Cuánta luz recibe el lugar donde la quieres poner (mucha / media / poca)?';
        $ai = '';
        $history = [['role'=>'user','content'=>'hola'], ['role'=>'assistant','content'=>'Hola']];

        $expr = $method->invoke($svc, $user, $ai, $history);
        $this->assertNotContains($expr, ['3-3','1-3'], 'Should not classify normal plant question as grief');
    }
}
