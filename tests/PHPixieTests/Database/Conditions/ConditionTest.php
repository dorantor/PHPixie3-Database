<?php
namespace PHPixieTests\Database\Conditions;

/**
 * @coversDefaultClass \PHPixie\Database\Conditions\Condition
 */
abstract class ConditionTest extends \PHPixieTests\AbstractDatabaseTest
{
    protected $condition;

    /**
     * @covers ::negate
     * @covers ::negated
     */
    public function testNegation()
    {
        $this->assertEquals(false, $this->condition->negated());
        $this->assertEquals($this->condition, $this->condition->negate());
        $this->assertEquals(true, $this->condition->negated());
    }

    /**
     * @covers ::logic
     * @covers ::setLogic
     */
    public function testLogic()
    {
        $this->assertEquals($this->condition, $this->condition->setLogic('or'));
        $this->assertEquals('or', $this->condition->logic());
        
        $this->setExpectedException('\PHPixie\Database\Exception\Builder');
        $this->condition->setLogic('test');
    }
}
