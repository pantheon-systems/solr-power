<?php
/**
 * Sample class for tests of grunt-phpcs runner
 *
 * @package Grunt\PhpCs
 */
namespace Grunt\PhpCs;

class World
{

    /**
     *
     * @var string
     */
    private $name = '';

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the world's name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns world's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
