<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\StatementContainer;

use Zend\Db\Adapter\Platform\Sql92 as DefaultAdapterPlatform;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Exception;
use Zend\Db\Adapter\AdapterInterface;

abstract class AbstractSql implements SqlInterface
{
    /**
     * @var array
     */
    protected $specifications = array();

    /**
     * @var string
     */
    protected $processInfo = array('paramPrefix' => '', 'subselectCount' => 0);

    /**
     * @var array
     */
    protected $instanceParameterIndex = array();

    /**
     * @var bool
     */
    protected $lock = false;

    /**
     * @var \Zend\Db\Sql\Platform\AbstractPlatform
     */
    protected static $sqlPlatform = null;

    /**
     *
     * @var \Zend\Db\Adapter\Platform\PlatformInterface
     */
    protected static $defaultAdapterPlatform = null;

    /**
     *
     * @return \Zend\Db\Sql\Platform\AbstractPlatform
     */
    public static function getSqlPlatform()
    {
        if (!self::$sqlPlatform) {
            self::$sqlPlatform = new \Zend\Db\Sql\Platform\AbstractPlatform();
        }
        return self::$sqlPlatform;
    }

    protected function processExpression(ExpressionInterface $expression, PlatformInterface $platform, DriverInterface $driver = null, $namedParameterPrefix = null)
    {
        // static counter for the number of times this method was invoked across the PHP runtime
        static $runtimeExpressionPrefix = 0;

        if ($driver && ((!is_string($namedParameterPrefix) || $namedParameterPrefix == ''))) {
            $namedParameterPrefix = sprintf('expr%04dParam', ++$runtimeExpressionPrefix);
        }

        $sql = '';
        $statementContainer = new StatementContainer;
        $parameterContainer = $statementContainer->getParameterContainer();

        // initialize variables
        $parts = $this->getSqlPlatform()
                    ->setSubject($expression)
                    ->setPlatform($platform)
                    ->getExpressionData();

        if (!isset($this->instanceParameterIndex[$namedParameterPrefix])) {
            $this->instanceParameterIndex[$namedParameterPrefix] = 1;
        }

        $expressionParamIndex = &$this->instanceParameterIndex[$namedParameterPrefix];

        foreach ($parts as $part) {

            // if it is a string, simply tack it onto the return sql "specification" string
            if (is_string($part)) {
                $sql .= $part;
                continue;
            }

            if (!is_array($part)) {
                throw new Exception\RuntimeException('Elements returned from getExpressionData() array must be a string or array.');
            }

            // process values and types (the middle and last position of the expression data)
            $values = $part[1];
            $types = (isset($part[2])) ? $part[2] : array();
            foreach ($values as $vIndex => $value) {
                if (isset($types[$vIndex]) && $types[$vIndex] == ExpressionInterface::TYPE_IDENTIFIER) {
                    $values[$vIndex] = $platform->quoteIdentifierInFragment($value);
                } elseif (isset($types[$vIndex]) && $types[$vIndex] == ExpressionInterface::TYPE_VALUE && $value instanceof Select) {
                    // process sub-select
                    if ($driver) {
                        $values[$vIndex] = '(' . $this->processSubSelect($value, $platform, $driver, $parameterContainer) . ')';
                    } else {
                        $values[$vIndex] = '(' . $this->processSubSelect($value, $platform) . ')';
                    }
                } elseif (isset($types[$vIndex]) && $types[$vIndex] == ExpressionInterface::TYPE_VALUE && $value instanceof ExpressionInterface) {
                    // recursive call to satisfy nested expressions
                    $innerStatementContainer = $this->processExpression($value, $platform, $driver, $namedParameterPrefix . $vIndex . 'subpart');
                    $values[$vIndex] = $innerStatementContainer->getSql();
                    if ($driver) {
                        $parameterContainer->merge($innerStatementContainer->getParameterContainer());
                    }
                } elseif (isset($types[$vIndex]) && $types[$vIndex] == ExpressionInterface::TYPE_VALUE) {

                    // if prepareType is set, it means that this particular value must be
                    // passed back to the statement in a way it can be used as a placeholder value
                    if ($driver) {
                        $name = $namedParameterPrefix . $expressionParamIndex++;
                        $parameterContainer->offsetSet($name, $value);
                        $values[$vIndex] = $driver->formatParameterName($name);
                        continue;
                    }

                    // if not a preparable statement, simply quote the value and move on
                    $values[$vIndex] = $platform->quoteValue($value);
                } elseif (isset($types[$vIndex]) && $types[$vIndex] == ExpressionInterface::TYPE_LITERAL) {
                    $values[$vIndex] = $value;
                }
            }

            // after looping the values, interpolate them into the sql string (they might be placeholder names, or values)
            $sql .= vsprintf($part[0], $values);
        }

        $statementContainer->setSql($sql);
        return $statementContainer;
    }

    /**
     * @param $specifications
     * @param $parameters
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function createSqlFromSpecificationAndParameters($specifications, $parameters)
    {
        if (is_string($specifications)) {
            return vsprintf($specifications, $parameters);
        }

        $parametersCount = count($parameters);
        foreach ($specifications as $specificationString => $paramSpecs) {
            if ($parametersCount == count($paramSpecs)) {
                break;
            }
            unset($specificationString, $paramSpecs);
        }

        if (!isset($specificationString)) {
            throw new Exception\RuntimeException(
                'A number of parameters was found that is not supported by this specification'
            );
        }

        $topParameters = array();
        foreach ($parameters as $position => $paramsForPosition) {
            if (isset($paramSpecs[$position]['combinedby'])) {
                $multiParamValues = array();
                foreach ($paramsForPosition as $multiParamsForPosition) {
                    $ppCount = count($multiParamsForPosition);
                    if (!isset($paramSpecs[$position][$ppCount])) {
                        throw new Exception\RuntimeException('A number of parameters (' . $ppCount . ') was found that is not supported by this specification');
                    }
                    $multiParamValues[] = vsprintf($paramSpecs[$position][$ppCount], $multiParamsForPosition);
                }
                $topParameters[] = implode($paramSpecs[$position]['combinedby'], $multiParamValues);
            } elseif ($paramSpecs[$position] !== null) {
                $ppCount = count($paramsForPosition);
                if (!isset($paramSpecs[$position][$ppCount])) {
                    throw new Exception\RuntimeException('A number of parameters (' . $ppCount . ') was found that is not supported by this specification');
                }
                $topParameters[] = vsprintf($paramSpecs[$position][$ppCount], $paramsForPosition);
            } else {
                $topParameters[] = $paramsForPosition;
            }
        }
        return vsprintf($specificationString, $topParameters);
    }

    protected function processSubSelect(Select $subselect, PlatformInterface $platform, DriverInterface $driver = null, ParameterContainer $parameterContainer = null)
    {
        if ($driver) {
            $stmtContainer = new StatementContainer;

            // Track subselect prefix and count for parameters
            $this->processInfo['subselectCount']++;
            $subselect->processInfo['subselectCount'] = $this->processInfo['subselectCount'];
            $subselect->processInfo['paramPrefix'] = 'subselect' . $subselect->processInfo['subselectCount'];

            // call subselect
            $subselect->prepareStatement(new \Zend\Db\Adapter\Adapter($driver, $platform), $stmtContainer);

            // copy count
            $this->processInfo['subselectCount'] = $subselect->processInfo['subselectCount'];

            $parameterContainer->merge($stmtContainer->getParameterContainer()->getNamedArray());
            $sql = $stmtContainer->getSql();
        } else {
            $sql = $subselect->getSqlString($platform);
        }
        return $sql;
    }

    /**
     * Get SQL string for statement
     *
     * @param  null|PlatformInterface $adapterPlatform If null, defaults to Sql92
     * @return string
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null)
    {
        if ($this->lock) {
            return $this->processSqlString($adapterPlatform);
        }
        try {
            $this->lock = true;
            $sqlString = $this->getSqlPlatform()
                            ->setSubject($this)
                            ->setPlatform($adapterPlatform)
                            ->getSqlString($adapterPlatform);
            $this->lock = false;
        } catch (\Exception $e) {
            $this->lock = false;
            throw $e;
        }
        return $sqlString;
    }

    abstract protected function processSqlString(PlatformInterface $adapterOrPlatform = null);

    /**
     *
     * @param null|PlatformInterface $adapterOrPlatform
     * @return PlatformInterface
     */
    protected function resolveAdapterPlatform($adapterOrPlatform)
    {
        if (!$adapterOrPlatform) {
            if (!self::$defaultAdapterPlatform) {
                self::$defaultAdapterPlatform = new DefaultAdapterPlatform();
            }
            return self::$defaultAdapterPlatform;
        } elseif ($adapterOrPlatform instanceof AdapterInterface) {
            return $adapterOrPlatform->getPlatform();
        } elseif ($adapterOrPlatform instanceof PlatformInterface) {
            return $adapterOrPlatform;
        }
        throw new Exception\InvalidArgumentException("adapterOrPlatform shoud be null, AdapterInterface or PlatformInterface");
    }

    /**
     * Localize variables for decorators (only)
     * @param type $sourse
     * @param array $excludeVariables
     * @return \Zend\Db\Sql\AbstractSql
     * @throws Exception\InvalidArgumentException
     */
    protected function localizeVariablesForDecorator($sourse, array $excludeVariables = array())
    {
        if (!$this instanceof PlatformDecoratorInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'function AbstractSql::localizeVariablesForDecorator can be call only for %s.',
                'Zend\Db\Sql\Platform\PlatformDecoratorInterface'
            ));
        }
        // localize variables
        foreach (get_object_vars($sourse) as $name => $value) {
            if (array_search($name, $excludeVariables) === false) {
                $this->{$name} = $value;
            }
        }
        return $this;
    }
}
