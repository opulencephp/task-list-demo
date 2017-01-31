<?php
namespace Project\Infrastructure;

use Opulence\Orm\IEntity;

/**
 * A task
 */
class Task implements IEntity
{
    /** @var string The Id of the task */
    private $id = "";
    /** @var string The text of the task */
    private $text = "";
    
    /**
     * @param string $id The Id of the task
     * @param string $text The text of the task
     */
    public function __construct(string $id, string $text)
    {
        $this->setId($id);
        $this->setText($text);
    }
    
    public function getId() : string
    {
        return $this->id;
    }
    
    public function getText() : string
    {
        return $this->text;
    }
    
    public function setId($id) : void
    {
        $this->id = $id;
    }
    
    public function setText(string $text) : void
    {
        $this->text = $text;
    }
}
