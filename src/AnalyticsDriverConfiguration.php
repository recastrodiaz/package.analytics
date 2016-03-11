<?php declare(strict_types = 1);

namespace Dms\Package\Analytics;
use Dms\Core\Form\Object\FormObject;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * The analytics driver configuration
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AnalyticsDriverConfiguration extends Entity
{
    const DRIVER_NAME = 'driverName';
    const OPTIONS = 'options';

    /**
     * @var string
     */
    public $driverName;

    /**
     * @var FormObject
     */
    public $options;

    /**
     * AnalyticsDriverConfiguration constructor.
     *
     * @param string     $driverName
     * @param FormObject $options
     */
    public function __construct(string $driverName, FormObject $options)
    {
        parent::__construct();

        $this->driverName = $driverName;
        $this->options    = $options;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->driverName)->asString();

        $class->property($this->options)->asObject(FormObject::class);
    }

    /**
     * @return IAnalyticsDriver
     */
    public function getDriver() : IAnalyticsDriver
    {
        return AnalyticsDriverFactory::load($this->driverName);
    }

    /**
     * @return IAnalyticsData
     */
    public function getAnalyticsData() : IAnalyticsData
    {
        return $this->getDriver()->getAnalyticsData($this->options);
    }

    /**
     * @return string
     */
    public function generateEmbedCode() : string
    {
        return $this->getDriver()->getEmbedCode($this->options);
    }
}