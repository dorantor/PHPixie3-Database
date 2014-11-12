<?php

namespace PHPixie\Database\Driver\PDO\Query\Type;

class Select extends \PHPixie\Database\Driver\PDO\Query\Items implements \PHPixie\Database\SQL\Query\Type\Select
{
    public function type()
    {
        return 'select';
    }

    public function fields($fields)
    {
        $this->builder->addFields(func_get_args());

        return $this;
    }

    public function clearFields()
    {
        $this->builder->clearArray('fields');

        return $this;
    }

    public function getFields()
    {
        return $this->builder->getArray('fields');
    }

    public function groupBy($fields)
    {
        $this->builder->addGroupBy(func_get_args());

        return $this;
    }

    public function clearGroupBy()
    {
        $this->builder->clearArray('groupBy');

        return $this;
    }

    public function getGroupBy()
    {
        return $this->builder->getArray('groupBy');
    }

    public function union($query, $all = false)
    {
        $this->builder->addUnion($query, $all);

        return $this;
    }

    public function clearUnions()
    {
        $this->builder->clearArray('unions');

        return $this;
    }

    public function getUnions()
    {
        return $this->builder->getArray('unions');
    }

    public function execute()
    {
        return parent::execute();
    }

    public function getHavingContainer()
    {
        return $this->builder->conditionContainer('having');
    }

    public function getHavingConditions()
    {
        return $this->builder->getConditions('having');
    }
    
    public function addHavingOperatorCondition($logic, $negate, $field, $operator, $values)
    {
        return $this->addContainerOperatorCondition($logic, $negate, $field, $operator, $values, 'having');
    }

    public function startHavingConditionGroup($logic = 'and', $negate = false)
    {
        return $this->startContainerConditionGroup($logic, $negate, 'having');
    }

    public function addHavingPlaceholder($logic = 'and', $negate = false, $allowEmpty = true)
    {
        return $this->addContainerPlaceholder($logic, $negate, $allowEmpty, 'having');
    }

    public function having()
    {
        return $this->addContainerCondition(func_get_args(), 'and', false, 'having');
    }

    public function andHaving()
    {
        return $this->addContainerCondition(func_get_args(), 'and', false, 'having');
    }

    public function orHaving()
    {
        return $this->addContainerCondition(func_get_args(), 'or', false, 'having');
    }

    public function xorHaving()
    {
        return $this->addContainerCondition(func_get_args(), 'xor', false, 'having');
    }

    public function havingNot()
    {
        return $this->addContainerCondition(func_get_args(), 'and', true, 'having');
    }

    public function andHavingNot()
    {
        return $this->addContainerCondition(func_get_args(), 'and', true, 'having');
    }

    public function orHavingNot()
    {
        return $this->addContainerCondition(func_get_args(), 'or', true, 'having');
    }

    public function xorHavingNot()
    {
        return $this->addContainerCondition(func_get_args(), 'xor', true, 'having');
    }

    public function startHavingGroup()
    {
        return $this->startContainerConditionGroup('and', false, 'having');
    }

    public function startAndHavingGroup()
    {
        return $this->startContainerConditionGroup('and', false, 'having');
    }

    public function startOrHavingGroup()
    {
        return $this->startContainerConditionGroup('or', false, 'having');
    }

    public function startXorHavingGroup()
    {
        return $this->startContainerConditionGroup('xor', false, 'having');
    }

    public function startHavingNotGroup()
    {
        return $this->startContainerConditionGroup('and', true, 'having');
    }

    public function startAndHavingNotGroup()
    {
        return $this->startContainerConditionGroup('and', true, 'having');
    }

    public function startOrHavingNotGroup()
    {
        return $this->startContainerConditionGroup('or', true, 'having');
    }

    public function startXorHavingNotGroup()
    {
        return $this->startContainerConditionGroup('xor', true, 'having');
    }

    public function endHavingGroup()
    {
        return $this->endContainerConditionGroup('having');
    }
}
