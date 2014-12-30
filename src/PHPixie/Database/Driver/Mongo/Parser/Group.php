<?php

namespace PHPixie\Database\Driver\Mongo\Parser;

use \PHPixie\Database\Type\Document\Conditions\Condition as DocumentCondition;

class Group extends \PHPixie\Database\Conditions\Logic\Parser
{
    protected $driver;
    protected $conditions;
    protected $operatorParser;

    public function __construct($driver, $conditions, $operatorParser)
    {
        $this->driver = $driver;
        $this->conditions = $conditions;
        $this->operatorParser = $operatorParser;
    }

    protected function normalize($condition)
    {
        if(
            $condition instanceof DocumentCondition\Placeholder\Embedded\SubarrayItem ||
            $condition instanceof DocumentCondition\Group\Embedded\SubarrayItem
          ) {
            $conditions = $condition->conditions();
            $parsed = $this->parse($conditions);
            
            $operatorCondition = $this->conditions->operator($condition->field, 'elemMatch', array($parsed));
            $this->copyLogicAndNegated($condition, $operatorCondition);
            
            return $this->normalizeOperatorCondition($operatorCondition);
        }
        
        if(
            $condition instanceof DocumentCondition\Placeholder\Embedded\Subdocument ||
            $condition instanceof DocumentCondition\Group\Embedded\Subdocument
          ) {
            $conditions = $condition->conditions();
            $conditions = $this->prefixConditions($condition->field(), $conditions);
            $group = $this->parseLogic($conditions);
            if ($group != null) {
                $this->copyLogicAndNegated($condition, $group);
            }
            
            return $group;
        }
        
        if (
            $condition instanceof \PHPixie\Database\Conditions\Condition\Group || 
            $condition instanceof \PHPixie\Database\Conditions\Condition\Placeholder
        ) {
            $group = $condition->conditions();
            $group = $this->parseLogic($group);

            if ($group != null) {
                $this->copyLogicAndNegated($condition, $group);
            }

            return $group;
        }

        if ($condition instanceof \PHPixie\Database\Conditions\Condition\Operator) {
            return $this->normalizeOperatorCondition($condition);
        }

        return $condition;

    }
    
    protected function normalizeOperatorCondition($condition)
    {
        $copy = $this->conditions->operator($condition->field, $condition->operator, $condition->values);
        $this->copyLogicAndNegated($condition, $copy);
        
        $expanded = $this->driver->expandedCondition();
        $expanded->add($copy);
        $expanded->setLogic($copy->logic());
        
        return $expanded;
    }
    
    protected function merge($left, $right)
    {
        if ($right->logic() === 'and') {
            return $left->add($right);

        } elseif ($right->logic() === 'or') {
            return $left->add($right, 'or');

        } else {
            $merged = $this->driver->expandedCondition();
            $rightClone = clone $right;
            $leftClone = clone $left;

            $merged->add($left);
            $merged->add($rightClone->negate());

            $rightPart = $this->driver->expandedCondition();
            $rightPart->add($leftClone->negate());
            $rightPart->add($right);

            $merged->add($rightPart, 'or');
            $merged->setLogic($left->logic());

            return $merged;
        }

    }

    public function parse($conditions)
    {
        $expanded = $this->parseLogic($conditions);
        $expanded = $this->normalize($expanded);

        if (empty($expanded))
            return array();

        $andGroups = array();
        foreach ($expanded->groups() as $group) {
            $andGroup = array();
            foreach ($group as $condition) {
                $condition = $this->operatorParser->parse($condition);
                foreach ($condition as $field => $fieldConditions) {
                    $appended = false;
                    foreach ($andGroup as $key=>$merged) {
                        if (!isset($merged[$field])) {
                            $andGroup[$key][$field] = $fieldConditions;
                            $appended = true;
                            break;
                        }
                    }
                    if (!$appended)
                        $andGroup[] = array($field => $fieldConditions);
                }
            }

            $count = count($andGroup);
            if ($count === 1) {
                $andGroup = current($andGroup);
            } else {
                $andGroup = array('$and' => $andGroup);
            }
            $andGroups[] = $andGroup;
        }

        $count = count($andGroups);
        if ($count === 1) {
            $andGroups = current($andGroups);
        } else {
            $andGroups = array('$or' => $andGroups);
        }

        return $andGroups;

    }
    
    protected function prefixConditions($prefix, $conditions)
    {
        $prefixed = array();
        foreach($conditions as $key => $condition) {
            if ($condition instanceof \PHPixie\Database\Conditions\Condition\Operator) {
                $copy = $this->conditions->operator(
                    $prefix.'.'.$condition->field,
                    $condition->operator,
                    $condition->values
                );
                $this->copyLogicAndNegated($condition, $copy);
                $condition = $copy;
                
            }elseif ($condition instanceof DocumentCondition\Placeholder\Embedded\S) {
                $condition->setField($prefix.'.'.$condition->field());                
                
            }elseif (
                $condition instanceof \PHPixie\Database\Conditions\Condition\Group ||
                $condition instanceof \PHPixie\Database\Conditions\Condition\Placeholder
            ) {
                $conditions = $condition->conditions();
                $conditions = $this->prefixConditions($prefix, $conditions);
                $copy = $this->conditions->group();
                $this->copyLogicAndNegated($condition, $copy);
                $copy->setConditions($conditions);
                $condition = $copy;
                
            }elseif ($condition instanceof \PHPixie\Database\Type\Document\Conditions\Condition\Placeholder\Subdocument) {
                $condition->setField($prefix.'.'.$condition->field());
            
            }elseif ($condition instanceof \PHPixie\Database\Conditions\Condition\Placeholder) {
                $conditions = $condition->conditions();
                $conditions = $this->prefixConditions($prefix, $conditions);
                $group = $this->conditions->group();
                $group->setConditions($conditions);
                $this->copyLogicAndNegated($condition, $group);
                $conditions[$key] = $group;
            }
            
            $
        }
    }
    
    protected function copyLogicAndNegated($source, $target)
    {
        $target->setLogic($source->logic());
        if($source->negated())
            $target->negate();
        
        return $target;
    }

}
